<?php namespace OFFLINE\Mall\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class BuilderTableCreateOfflineMallProducts extends Migration
{
    public function up()
    {
        Schema::create('offline_mall_products', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('category_id')->nullable();
            $table->string('user_defined_id')->nullable();
            $table->string('name', 255);
            $table->string('slug', 255);
            $table->integer('price')->unsigned();
            $table->integer('old_price')->unsigned()->nullable();
            $table->string('description_short', 255)->nullable();
            $table->text('description')->nullable();
            $table->string('meta_title', 255)->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->integer('weight')->nullable()->unsigned();
            $table->integer('quantity_default')->nullable()->unsigned();
            $table->integer('quantity_min')->nullable()->unsigned();
            $table->integer('quantity_max')->nullable()->unsigned();
            $table->integer('stock')->nullable();
            $table->text('links')->nullable();
            $table->string('inventory_management_method')->default('single');
            $table->boolean('allow_out_of_stock_purchases')->default(false);
            $table->boolean('stackable')->default(true);
            $table->boolean('shippable')->default(true);
            $table->boolean('price_includes_tax')->default(true);
            $table->integer('group_by_property_id')->nullable();
            $table->boolean('published')->default(false);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('offline_mall_products');
    }
}
