<?php

namespace OFFLINE\Mall\Updates;

use DB;
use October\Rain\Database\Updates\Migration;
use Schema;

class MultiConditionDiscounts extends Migration
{
    public function up()
    {
        // Conditions table
        Schema::create('offline_mall_discount_conditions', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->unsignedInteger('discount_id');
            $table->string('trigger');
            $table->string('code')->nullable();
            $table->unsignedInteger('product_id')->nullable();
            $table->unsignedInteger('minimum_quantity')->nullable();
            $table->unsignedInteger('customer_group_id')->nullable();
            $table->unsignedInteger('payment_method_id')->nullable();
            $table->decimal('minimum_total', 15, 2)->nullable();
            $table->text('shipping_method_ids')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('discount_id')
                ->references('id')
                ->on('offline_mall_discounts')
                ->onDelete('cascade');
        });

        // AND/OR operator on the discount
        Schema::table('offline_mall_discounts', function ($table) {
            $table->string('conditions_operator')->default('and')->after('trigger');
        });

        // Pivot: track which codes have been applied per cart-discount pair
        Schema::table('offline_mall_cart_discount', function ($table) {
            $table->text('applied_codes')->nullable()->after('discount_id');
        });

        // Migrate existing single-trigger discounts to condition rows
        $discounts = DB::table('offline_mall_discounts')
            ->whereNotNull('trigger')
            ->where('trigger', '!=', '')
            ->get();

        foreach ($discounts as $discount) {
            $existing = DB::table('offline_mall_discount_conditions')
                ->where('discount_id', $discount->id)
                ->count();

            if ($existing > 0) {
                continue;
            }

            DB::table('offline_mall_discount_conditions')->insert([
                'discount_id'       => $discount->id,
                'trigger'           => $discount->trigger,
                'code'              => $discount->trigger === 'code' ? $discount->code : null,
                'product_id'        => $discount->trigger === 'product' ? $discount->product_id : null,
                'customer_group_id' => $discount->trigger === 'customer_group' ? $discount->customer_group_id : null,
                'payment_method_id' => $discount->trigger === 'payment_method' ? $discount->payment_method_id : null,
                'minimum_total'     => $this->migrateMinimumTotal($discount),
                'shipping_method_ids' => $this->migrateShippingMethodIds($discount),
                'sort_order'        => 0,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        }
    }

    public function down()
    {
        Schema::table('offline_mall_cart_discount', function ($table) {
            $table->dropColumn('applied_codes');
        });

        Schema::table('offline_mall_discounts', function ($table) {
            $table->dropColumn('conditions_operator');
        });

        Schema::dropIfExists('offline_mall_discount_conditions');
    }

    private function migrateMinimumTotal(object $discount): ?float
    {
        if ($discount->trigger !== 'total') {
            return null;
        }

        $price = DB::table('offline_mall_prices')
            ->where('priceable_type', 'mall.discount')
            ->where('priceable_id', $discount->id)
            ->where('field', 'totals_to_reach')
            ->orderBy('id')
            ->first();

        return $price && $price->price !== null ? $price->price / 100 : null;
    }

    private function migrateShippingMethodIds(object $discount): ?string
    {
        if ($discount->trigger !== 'shipping_method') {
            return null;
        }

        $ids = DB::table('offline_mall_shipping_method_discount')
            ->where('discount_id', $discount->id)
            ->pluck('shipping_method_id')
            ->toArray();

        return count($ids) > 0 ? json_encode($ids) : null;
    }
}
