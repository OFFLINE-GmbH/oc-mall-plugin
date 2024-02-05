<?php namespace OFFLINE\Mall\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateOfflineMallProperties extends Migration
{
    public function up()
    {
        Schema::create('offline_mall_properties', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name');
            $table->string('slug')->unique()->nullable();
            $table->string('type');
            $table->string('unit')->nullable();
            $table->text('options')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();

            if ( ! app()->runningUnitTests()) {
                $table->index('deleted_at', 'idx_property_deleted_at');
            }
        });
    }

    public function down()
    {
        Schema::dropIfExists('offline_mall_properties');
    }
}
