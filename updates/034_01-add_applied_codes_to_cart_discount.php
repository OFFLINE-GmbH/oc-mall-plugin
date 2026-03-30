<?php

namespace OFFLINE\Mall\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

/**
 * Adds applied_codes to the cart-discount pivot so the applier can verify which
 * specific code conditions have been satisfied for AND-logic multi-code discounts.
 */
class AddAppliedCodesToCartDiscount extends Migration
{
    public function up()
    {
        Schema::table('offline_mall_cart_discount', function ($table) {
            $table->text('applied_codes')->nullable()->after('discount_id');
        });
    }

    public function down()
    {
        Schema::table('offline_mall_cart_discount', function ($table) {
            $table->dropColumn('applied_codes');
        });
    }
}
