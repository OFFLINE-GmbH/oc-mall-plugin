<?php

namespace OFFLINE\Mall\Updates;

use DB;
use October\Rain\Database\Updates\Migration;

/**
 * Step B: copies existing single-trigger discount data into the new
 * offline_mall_discount_conditions table so all discounts work with
 * the multi-condition system after this migration runs.
 *
 * The legacy columns (trigger, product_id, customer_group_id,
 * payment_method_id, code) are left in place and will be dropped
 * by migration 033_01 in a future breaking release.
 */
class MigrateDiscountTriggersToConditions extends Migration
{
    public function up()
    {
        $discounts = DB::table('offline_mall_discounts')
            ->whereNotNull('trigger')
            ->where('trigger', '!=', '')
            ->get();

        foreach ($discounts as $discount) {
            // Skip discounts that already have conditions (e.g. re-run safety)
            $existing = DB::table('offline_mall_discount_conditions')
                ->where('discount_id', $discount->id)
                ->count();

            if ($existing > 0) {
                continue;
            }

            $conditionId = DB::table('offline_mall_discount_conditions')->insertGetId([
                'discount_id'       => $discount->id,
                'trigger'           => $discount->trigger,
                'code'              => $discount->trigger === 'code' ? $discount->code : null,
                'product_id'        => $discount->trigger === 'product' ? $discount->product_id : null,
                'customer_group_id' => $discount->trigger === 'customer_group' ? $discount->customer_group_id : null,
                'payment_method_id' => $discount->trigger === 'payment_method' ? $discount->payment_method_id : null,
                'sort_order'        => 0,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            // Copy shipping_method pivot rows to the new condition pivot table
            if ($discount->trigger === 'shipping_method') {
                $pivots = DB::table('offline_mall_shipping_method_discount')
                    ->where('discount_id', $discount->id)
                    ->get();

                foreach ($pivots as $pivot) {
                    DB::table('offline_mall_discount_condition_shipping_method')->insertOrIgnore([
                        'condition_id'       => $conditionId,
                        'shipping_method_id' => $pivot->shipping_method_id,
                    ]);
                }
            }

            // Copy totals_to_reach prices for 'total' trigger discounts
            if ($discount->trigger === 'total') {
                $prices = DB::table('offline_mall_prices')
                    ->where('priceable_type', 'mall.discount')
                    ->where('priceable_id', $discount->id)
                    ->where('field', 'totals_to_reach')
                    ->get();

                foreach ($prices as $price) {
                    DB::table('offline_mall_prices')->insert([
                        'priceable_type'    => 'mall.discount_condition',
                        'priceable_id'      => $conditionId,
                        'currency_id'       => $price->currency_id,
                        'price_category_id' => $price->price_category_id,
                        'price'             => $price->price,
                        'field'             => 'totals_to_reach',
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ]);
                }
            }
        }
    }

    public function down()
    {
        // Remove all conditions that were created from legacy trigger data.
        // We can identify them by checking if the parent discount still has
        // a matching legacy trigger column set.
        DB::table('offline_mall_discount_conditions as c')
            ->join('offline_mall_discounts as d', 'd.id', '=', 'c.discount_id')
            ->whereRaw('d.trigger = c.trigger')
            ->delete();
    }
}
