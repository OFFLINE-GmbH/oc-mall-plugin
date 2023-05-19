<?php
  
use October\Rain\Database\Updates\Migration;
use Schema;

class AddProductVariantSortOrder extends Migration
{
    public function up()
    {
        Schema::table('offline_mall_product_variants', function ($table) {
            $table->unsignedinteger('sort_order')->nullable()->index();
        });
    }

    public function down()
    {
        Schema::table('offline_mall_product_variants', function ($table) {
            $table->dropColumn('sort_order');
        });
    }
};
