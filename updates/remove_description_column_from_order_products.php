<?php namespace OFFLINE\Mall\Updates;

use Artisan;
use DB;
use October\Rain\Database\Updates\Migration;
use Schema;

class RemoveDescriptionColumnFromOrderProducts extends Migration
{
    public function up()
    {
        Schema::table('offline_mall_order_products', function ($table) {
            if (Schema::hasColumn('offline_mall_order_products', 'description')) {
                $table->dropColumn(['description']);
            }
        });
    }

    public function down()
    {
        Schema::table('offline_mall_order_products', function ($table) {
            $table->longText('description');
        });
    }
}