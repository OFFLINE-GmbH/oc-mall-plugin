<?php namespace OFFLINE\Mall\Updates;

use Artisan;
use DB;
use October\Rain\Database\Updates\Migration;
use Schema;

class RemovePaymentDataColumnFromOrdersTable extends Migration
{
    public function up()
    {
        Schema::table('offline_mall_orders', function ($table) {
            if (Schema::hasColumn('offline_mall_orders', 'payment_data')) {
                $table->dropColumn(['payment_data']);
            }
        });
    }

    public function down()
    {
        Schema::table('offline_mall_orders', function ($table) {
            $table->mediumText('payment_data');
        });
    }
}