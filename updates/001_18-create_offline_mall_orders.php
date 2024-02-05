<?php namespace OFFLINE\Mall\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class CreateOfflineMallOrders extends Migration
{
    public function up()
    {
        Schema::create('offline_mall_orders', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('session_id')->nullable();
            $table->integer('order_number')->nullable()->unique();
            $table->string('invoice_number')->nullable();
            $table->text('currency')->nullable();
            $table->string('payment_state');
            $table->integer('order_state_id');
            $table->boolean('shipping_address_same_as_billing')->nullable();
            $table->text('billing_address')->nullable();
            $table->text('shipping_address')->nullable();
            $table->text('custom_fields')->nullable();
            $table->text('shipping')->nullable();
            $table->text('taxes')->nullable();
            $table->text('payment')->nullable();
            $table->text('discounts')->nullable();
            $table->integer('payment_method_id')->nullable();
            $table->text('payment_data')->nullable();
            $table->text('payment_id')->nullable();
            $table->string('payment_hash')->nullable();
            $table->integer('total_shipping_pre_taxes')->nullable();
            $table->integer('total_shipping_taxes')->nullable();
            $table->integer('total_shipping_post_taxes')->nullable();
            $table->integer('total_payment_pre_taxes')->nullable();
            $table->integer('total_payment_taxes')->nullable();
            $table->integer('total_payment_post_taxes')->nullable();
            $table->integer('total_product_pre_taxes')->nullable();
            $table->integer('total_product_taxes')->nullable();
            $table->integer('total_product_post_taxes')->nullable();
            $table->integer('total_taxes')->nullable();
            $table->integer('total_pre_payment')->nullable();
            $table->integer('total_pre_taxes')->nullable();
            $table->integer('total_post_taxes')->nullable();
            $table->string('tracking_number')->nullable();
            $table->string('tracking_url')->nullable();
            $table->string('credit_card_last4_digits', 4)->nullable();
            $table->string('card_holder_name')->nullable();
            $table->string('card_type')->nullable();
            $table->string('lang');
            $table->integer('total_weight')->nullable();
            $table->text('customer_notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->string('payment_transaction_id')->nullable();
            $table->string('ip_address')->nullable();
            $table->integer('customer_id')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('offline_mall_orders');
    }
}
