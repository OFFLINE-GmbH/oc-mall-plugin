<?php namespace OFFLINE\Mall\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateOfflineMallNotifications extends Migration
{
    public function up()
    {
        Schema::create('offline_mall_notifications', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->boolean('enabled')->default(1);
            $table->string('code');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('template');
            $table->integer('sort_order')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('offline_mall_notifications');
    }
}
