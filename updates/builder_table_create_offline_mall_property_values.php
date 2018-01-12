<?php namespace OFFLINE\Mall\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class BuilderTableCreateOfflineMallPropertyValues extends Migration
{
    public function up()
    {
        Schema::create('offline_mall_property_values', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('describable_id')->unsigned()->nullable();
            $table->string('describable_type')->nullable();
            $table->integer('property_id');
            $table->text('value')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('offline_mall_property_values');
    }
}
