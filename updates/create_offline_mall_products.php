<?php namespace OFFLINE\Mall\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class CreateOfflineMallProducts extends Migration
{
    public function up()
    {
        Schema::create('offline_mall_products', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('category_id')->nullable();
            $table->integer('brand_id')->nullable();
            $table->string('user_defined_id')->nullable();
            $table->string('name', 255);
            $table->string('slug', 191);
            $table->string('description_short', 255)->nullable();
            $table->text('description')->nullable();
            $table->string('meta_title', 255)->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->longText('additional_descriptions')->nullable();
            $table->text('additional_properties')->nullable();
            $table->integer('weight')->nullable()->unsigned();
            $table->integer('quantity_default')->nullable()->unsigned();
            $table->integer('quantity_min')->nullable()->unsigned();
            $table->integer('quantity_max')->nullable()->unsigned();
            $table->integer('stock')->default(0);
            $table->text('links')->nullable();
            $table->string('inventory_management_method')->default('single');
            $table->boolean('allow_out_of_stock_purchases')->default(false);
            $table->boolean('stackable')->default(true);
            $table->boolean('shippable')->default(true);
            $table->boolean('price_includes_tax')->default(true);
            $table->integer('group_by_property_id')->nullable();
            $table->boolean('published')->default(false);
            $table->integer('sales_count')->default(0)->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();

            if ( ! app()->runningUnitTests()) {
                $table->index('deleted_at', 'idx_product_deleted_at');
                $table->index('slug', 'idx_product_slug');
                $table->index('category_id', 'idx_product_category_id');
            }
        });
    }

    public function down()
    {
        Schema::dropIfExists('offline_mall_products');
    }
}
