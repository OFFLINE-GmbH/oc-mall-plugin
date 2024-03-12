<?php namespace OFFLINE\Mall\Updates;

use Artisan;
use DB;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use Schema;

class UpdateJsonableColumnsToMediumtext extends Migration
{
    public function up()
    {
        Schema::table('offline_mall_payments_log', function (Blueprint $table) {
            $table->mediumText('payment_method')->change();
            $table->mediumText('data')->change();
            $table->longText('order_data')->change();
        });
        Schema::table('offline_mall_orders', function (Blueprint $table) {
            $table->mediumText('currency')->change();
            $table->mediumText('billing_address')->change();
            $table->mediumText('shipping_address')->change();
            $table->mediumText('shipping')->change();
            $table->mediumText('taxes')->change();
            $table->mediumText('payment')->change();
            $table->mediumText('payment_data')->change();
        });
        Schema::table('offline_mall_order_products', function (Blueprint $table) {
            $table->mediumText('property_values')->change();
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