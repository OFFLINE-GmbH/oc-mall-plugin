<?php namespace OFFLINE\Mall\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateOfflineMallDiscountsVariants extends Migration
{
    public function up()
    {
        Schema::create('offline_mall_discount_variant', function ($table) {
            $table->engine = 'InnoDB';
            $table->integer('discount_id')->unsigned();
            $table->integer('variant_id')->unsigned();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('offline_mall_discount_variant');
    }
}
