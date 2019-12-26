<?php namespace OFFLINE\Mall\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class CreateOfflineMallWishlistItems extends Migration
{
    public function up()
    {
        Schema::create('offline_mall_wishlist_items', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('wishlist_id')->index();
            $table->integer('product_id')->index();
            $table->integer('variant_id')->nullable()->index();
            $table->integer('quantity')->default(1);

            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('offline_mall_wishlist_items');
    }
}
