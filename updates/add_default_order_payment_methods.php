<?php namespace OFFLINE\Mall\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddDefaultOrderPaymentMethods extends Migration
{
    public function up()
    {
        Schema::table('offline_mall_payment_methods', function($table)
        {
            $table->integer('sort_order')->default(1)->change();
        });
    }
    
    public function down()
    {
        Schema::table('offline_mall_payment_methods', function($table)
        {
            $table->integer('sort_order')->default(null)->change();
        });
    }
}
