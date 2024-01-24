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
        // Leave the columns. The migration might fail if data gets truncated.
    }
}