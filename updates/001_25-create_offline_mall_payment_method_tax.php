<?php namespace OFFLINE\Mall\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateOfflineMallPaymentMethodTax extends Migration
{
    public function up()
    {
        Schema::create('offline_mall_payment_method_tax', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('payment_method_id')->unsigned();
            $table->integer('tax_id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('offline_mall_payment_method_tax');
    }
}
