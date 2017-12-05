<?php namespace OFFLINE\Mall\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class BuilderTableCreateOfflineMallProductVariants extends Migration
{
    public function up()
    {
        Schema::create('offline_mall_product_variants', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('product_id')->unsigned();
            $table->integer('stock')->nullable();
            $table->boolean('allow_out_of_stock_purchases')->default(false);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('offline_mall_product_variants');
    }
}
