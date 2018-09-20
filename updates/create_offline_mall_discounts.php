<?php namespace OFFLINE\Mall\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class CreateOfflineMallDiscounts extends Migration
{
    public function up()
    {
        Schema::create('offline_mall_discounts', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name');
            $table->string('code')->nullable();
            $table->integer('product_id')->nullable();
            $table->string('type')->default('Rate');
            $table->string('trigger')->default('Code');
            $table->integer('rate')->nullable()->unsigned();
            $table->integer('max_number_of_usages')->nullable()->unsigned();
            $table->dateTime('expires')->nullable();
            $table->integer('number_of_usages')->default(0)->unsigned();
            $table->string('shipping_description')->nullable();
            $table->integer('shipping_guaranteed_days_to_delivery')->nullable()->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('offline_mall_discounts');
    }
}
