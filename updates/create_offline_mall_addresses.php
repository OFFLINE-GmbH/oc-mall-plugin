<?php namespace OFFLINE\Mall\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateOfflineMallAddresses extends Migration
{
    public function up()
    {
        Schema::create('offline_mall_addresses', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('company')->nullable();
            $table->string('name')->nullable();
            $table->text('lines');
            $table->string('zip', 20);
            $table->string('city');
            $table->integer('state_id')->unsigned()->nullable()->index();
            $table->integer('country_id')->unsigned()->nullable()->index();
            $table->text('details')->nullable();
            $table->integer('customer_id')->unsigned()->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('offline_mall_addresses');
    }
}
