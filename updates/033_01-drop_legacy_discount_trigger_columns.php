<?php

namespace OFFLINE\Mall\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

/**
 * Step C (breaking): drops the legacy single-trigger columns from
 * offline_mall_discounts now that all discount logic uses the
 * offline_mall_discount_conditions table.
 *
 * Run this migration only after confirming that:
 *   - Migration 032_03 has run on all environments
 *   - No third-party code reads $discount->trigger, ->product_id,
 *     ->customer_group_id, ->payment_method_id, or ->code directly
 *
 * This migration is intentionally kept in a separate version so it
 * can be released as a distinct breaking change.
 */
class DropLegacyDiscountTriggerColumns extends Migration
{
    public function up()
    {
        Schema::table('offline_mall_discounts', function ($table) {
            $table->dropColumn([
                'trigger',
                'code',
                'product_id',
                'customer_group_id',
                'payment_method_id',
            ]);
        });
    }

    public function down()
    {
        Schema::table('offline_mall_discounts', function ($table) {
            $table->string('trigger')->nullable();
            $table->string('code')->nullable();
            $table->integer('product_id')->nullable();
            $table->unsignedInteger('customer_group_id')->nullable();
            $table->unsignedInteger('payment_method_id')->nullable();
        });
    }
}
