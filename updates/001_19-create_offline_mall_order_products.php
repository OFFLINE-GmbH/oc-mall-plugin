<?php namespace OFFLINE\Mall\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class CreateOfflineMallOrderProducts extends Migration
{
    public function up()
    {
        Schema::create('offline_mall_order_products', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('order_id');
            $table->integer('product_id');
            $table->integer('variant_id')->nullable();

            $table->string('name');
            $table->string('variant_name')->nullable();
            $table->text('description')->nullable();
            $table->integer('quantity');

            $table->integer('price_pre_taxes');
            $table->integer('price_taxes');
            $table->integer('price_post_taxes');

            $table->integer('total_pre_taxes');
            $table->integer('total_taxes');
            $table->integer('total_post_taxes');
            $table->decimal('tax_factor');

            $table->integer('weight')->nullable();
            $table->integer('width')->nullable();
            $table->integer('length')->nullable();
            $table->integer('height')->nullable();

            $table->integer('total_weight')->nullable();

            $table->boolean('stackable');
            $table->boolean('shippable');

            $table->text('property_values')->nullable();
            $table->text('properties_description')->nullable();
            $table->text('custom_field_values');
            $table->text('taxes');
            $table->text('item');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('offline_mall_order_products');
    }
}
