<?php namespace OFFLINE\Mall\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddIsDefaultColumnToTaxesTable extends Migration
{
    public function up()
    {
        Schema::table('offline_mall_taxes', function($table)
        {
            $table->boolean('is_default')->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('offline_mall_taxes', function($table)
        {
            $table->dropColumn('is_default');
        });
    }
}
