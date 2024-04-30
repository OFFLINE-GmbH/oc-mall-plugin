<?php namespace OFFLINE\Mall\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddEmbedsColumnToProductsTable extends Migration
{
    public function up()
    {
        Schema::table('offline_mall_products', function($table)
        {
            $table->text('embeds')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('offline_mall_products', function($table)
        {
            $table->dropColumn('embeds');
        });
    }
}
