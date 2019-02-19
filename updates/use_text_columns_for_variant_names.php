<?php namespace OFFLINE\Mall\Updates;

use Artisan;
use DB;
use October\Rain\Database\Updates\Migration;
use Schema;

class UseTextColumnsForVariantNames extends Migration
{
    public function up()
    {
        Schema::table('offline_mall_order_products', function ($table) {
            $table->text('variant_name')->change();
        });
    }

    public function down()
    {
        Schema::table('offline_mall_order_products', function ($table) {
            $table->string('variant_name')->change();
        });
    }
}