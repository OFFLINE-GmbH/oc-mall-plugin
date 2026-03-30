<?php

namespace OFFLINE\Mall\Updates;

use DB;
use October\Rain\Database\Updates\Migration;
use Schema;

class AddMinimumTotalAndShippingIdsToConditions extends Migration
{
    public function up()
    {
        Schema::table('offline_mall_discount_conditions', function ($table) {
            // Stores the minimum cart total for 'total' trigger conditions.
            // Value is in the store's default currency (e.g. 100.00 for €100).
            $table->decimal('minimum_total', 15, 2)->nullable()->after('payment_method_id');

            // Stores a JSON-encoded array of shipping method IDs for 'shipping_method' conditions.
            $table->text('shipping_method_ids')->nullable()->after('minimum_total');
        });

        // Backfill minimum_total from Price records created by migration 032_03
        $conditions = DB::table('offline_mall_discount_conditions')
            ->where('trigger', 'total')
            ->get();

        foreach ($conditions as $condition) {
            $price = DB::table('offline_mall_prices')
                ->where('priceable_type', 'mall.discount_condition')
                ->where('priceable_id', $condition->id)
                ->where('field', 'totals_to_reach')
                ->orderBy('id')
                ->first();

            if ($price && $price->price !== null) {
                DB::table('offline_mall_discount_conditions')
                    ->where('id', $condition->id)
                    ->update(['minimum_total' => $price->price / 100]);
            }
        }

        // Backfill shipping_method_ids from the pivot table created by migration 032_03
        $shippingConditions = DB::table('offline_mall_discount_conditions')
            ->where('trigger', 'shipping_method')
            ->get();

        foreach ($shippingConditions as $condition) {
            $ids = DB::table('offline_mall_discount_condition_shipping_method')
                ->where('condition_id', $condition->id)
                ->pluck('shipping_method_id')
                ->toArray();

            if (count($ids) > 0) {
                DB::table('offline_mall_discount_conditions')
                    ->where('id', $condition->id)
                    ->update(['shipping_method_ids' => json_encode($ids)]);
            }
        }
    }

    public function down()
    {
        Schema::table('offline_mall_discount_conditions', function ($table) {
            $table->dropColumn(['minimum_total', 'shipping_method_ids']);
        });
    }
}
