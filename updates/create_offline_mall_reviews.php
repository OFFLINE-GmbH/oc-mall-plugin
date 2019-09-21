<?php namespace OFFLINE\Mall\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class CreateOfflineMallReviews extends Migration
{
    public function up()
    {
        Schema::create('offline_mall_review_categories', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
        Schema::create('offline_mall_category_review_category', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('category_id');
            $table->integer('review_category_id');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique(['category_id', 'review_category_id'], 'unq_review_category_id');
            $table->index(['category_id', 'review_category_id'], 'idx_review_category_id');
        });
        Schema::create('offline_mall_reviews', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('product_id')->index();
            $table->integer('variant_id')->nullable()->index();
            $table->integer('customer_id')->nullable();
            $table->tinyInteger('rating');
            $table->string('user_hash');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->text('pros')->nullable();
            $table->text('cons')->nullable();

            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
        Schema::create('offline_mall_category_reviews', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('review_id')->index();
            $table->integer('review_category_id');
            $table->tinyInteger('rating');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
        Schema::table('offline_mall_categories', function ($table) {
            $table->boolean('inherit_review_categories')->after('inherit_property_groups')->default(0);
        });
    }

    public function down()
    {
        Schema::dropIfExists('offline_mall_review_categories');
        Schema::dropIfExists('offline_mall_category_review_category');
        Schema::dropIfExists('offline_mall_reviews');
        Schema::dropIfExists('offline_mall_category_reviews');
        Schema::table('offline_mall_categories', function ($table) {
            $table->dropColumn(['inherit_review_categories']);
        });
    }
}
