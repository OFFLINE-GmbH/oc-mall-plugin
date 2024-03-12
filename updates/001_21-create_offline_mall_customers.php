<?php namespace OFFLINE\Mall\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class CreateOfflineMallCustomers extends Migration
{
    public function up()
    {
        Schema::create('offline_mall_customers', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('firstname');
            $table->string('lastname');
            $table->boolean('is_guest')->default(0);
            $table->integer('user_id')->nullable()->unsigned();
            $table->integer('default_shipping_address_id')->nullable();
            $table->integer('default_billing_address_id')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('offline_mall_customers');
    }
}
