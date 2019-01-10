<?php namespace OFFLINE\Mall\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class CreateOfflineMallProductVariants extends Migration
{
    public function up()
    {
        Schema::create('offline_mall_product_variants', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('product_id')->unsigned();
            $table->string('user_defined_id')->nullable();
            $table->integer('image_set_id')->unsigned()->nullable();
            $table->integer('stock')->default(0);
            $table->string('name')->nullable();
            $table->integer('weight')->nullable()->unsigned();
            $table->boolean('published')->default(true);
            $table->integer('sales_count')->default(0)->unsigned();
            $table->boolean('allow_out_of_stock_purchases')->default(false);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();

            if ( ! app()->runningUnitTests()) {
                $table->index('product_id', 'idx_product_variant_product_id');
            }
        });
    }

    public function down()
    {
        Schema::dropIfExists('offline_mall_product_variants');
    }
}
