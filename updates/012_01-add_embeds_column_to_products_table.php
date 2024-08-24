<?php

namespace OFFLINE\Mall\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class AddEmbedsColumnToProductsTable extends Migration
{
    public function up()
    {
        Schema::table('offline_mall_products', function ($table) {
            $table->text('embeds')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('offline_mall_products', function ($table) {
            $table->dropColumn('embeds');
        });
    }
}
