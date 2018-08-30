<?php namespace OFFLINE\Mall\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateOfflineMallCurrencies extends Migration
{
    public function up()
    {
        Schema::create('offline_mall_currencies', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('code');
            $table->string('symbol')->nullable();
            $table->integer('rate')->default(1);
            $table->integer('decimals')->default(2);
            $table->text('format');
            $table->integer('sort_order')->nullable();
            $table->boolean('is_default')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('offline_mall_currencies');
    }
}
