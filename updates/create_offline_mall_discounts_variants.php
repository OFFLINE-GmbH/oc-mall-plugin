<?php namespace OFFLINE\Mall\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateOfflineMallDiscountsVariants extends Migration
{
    public function up()
    {
        Schema::create('offline_mall_discounts_variants', function ($table) {
            $table->engine = 'InnoDB';
            $table->integer('discount_id')->unsigned();
            $table->integer('variant_id')->unsigned();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('offline_mall_discounts_variants');
    }
}