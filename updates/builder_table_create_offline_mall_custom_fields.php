<?php namespace OFFLINE\Mall\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class BuilderTableCreateOfflineMallProductCustomFields extends Migration
{
    public function up()
    {
        Schema::create('offline_mall_custom_fields', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name');
            $table->string('type')->default('text');
            $table->text('options')->nullable();
            $table->boolean('required')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('offline_mall_custom_fields');
    }
}
