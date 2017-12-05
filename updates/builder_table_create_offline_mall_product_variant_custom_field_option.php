<?php namespace OFFLINE\Mall\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateOfflineMallProductVariantCustomFieldOption extends Migration
{
    public function up()
    {
        Schema::create('offline_mall_product_variant_custom_field_option', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('variant_id')->unsigned();
            $table->integer('custom_field_option_id')->unsigned();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('offline_mall_product_variant_custom_field_option');
    }
}
