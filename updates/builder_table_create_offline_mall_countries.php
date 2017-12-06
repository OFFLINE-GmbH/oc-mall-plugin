<?php namespace OFFLINE\Mall\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateOfflineMallCountries extends Migration
{
    public function up()
    {
        Schema::create('offline_mall_countries', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('code');
            $table->string('name');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('offline_mall_countries');
    }
}
