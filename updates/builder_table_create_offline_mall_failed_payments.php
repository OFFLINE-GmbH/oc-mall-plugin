<?php namespace OFFLINE\Mall\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class BuilderTableCreateOfflineMallFailedPayments extends Migration
{
    public function up()
    {
        Schema::create('offline_mall_failed_payments', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->text('data')->nullable();
            $table->integer('order_id')->nullable();
            $table->text('order_data')->nullable();
            $table->string('message')->nullable();
            $table->string('code')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('offline_mall_failed_payments');
    }
}
