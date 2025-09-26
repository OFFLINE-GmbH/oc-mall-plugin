<?php

namespace OFFLINE\Mall\Updates;

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use Schema;

class UpdateJsonableColumnsToMediumtext extends Migration
{
    public function up()
    {
        Schema::table('offline_mall_payments_log', function (Blueprint $table) {
            $table->mediumText('payment_method')->nullable()->change();
            $table->mediumText('data')->nullable()->change();
            $table->longText('order_data')->nullable()->change();
        });
        Schema::table('offline_mall_orders', function (Blueprint $table) {
            $table->mediumText('currency')->nullable()->change();
            $table->mediumText('billing_address')->nullable()->change();
            $table->mediumText('shipping_address')->nullable()->change();
            $table->mediumText('shipping')->nullable()->change();
            $table->mediumText('taxes')->nullable()->change();
            $table->mediumText('payment')->nullable()->change();
            $table->mediumText('payment_data')->nullable()->change();
        });
        Schema::table('offline_mall_order_products', function (Blueprint $table) {
            $table->mediumText('property_values')->nullable()->change();
            $table->longText('custom_field_values')->change();
            $table->mediumText('taxes')->change();
            $table->mediumText('item')->change();
        });
    }

    public function down()
    {
        // Leave the columns. The migration might fail if data gets truncated.
    }
}
