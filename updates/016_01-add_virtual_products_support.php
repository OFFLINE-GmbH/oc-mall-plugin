<?php namespace OFFLINE\Mall\Updates;

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use OFFLINE\Mall\Models\Notification;
use Schema;

class AddVirtualProductsSupport extends Migration
{
    public function up()
    {
        Schema::table('offline_mall_products', function (Blueprint $table) {
            $table->boolean('is_virtual')->default(false);
            $table->integer('file_expires_after_days')->unsigned()->nullable();
            $table->integer('file_max_download_count')->unsigned()->nullable();
            $table->boolean('file_session_required')->default(0);
        });
        Schema::create('offline_mall_product_files', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('product_id')->unsigned();
            $table->string('version');
            $table->string('display_name');
            $table->integer('download_count')->default(0);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
        Schema::create('offline_mall_product_file_grants', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('order_product_id')->unsigned();
            $table->integer('download_count')->default(0)->unsigned();
            $table->integer('max_download_count')->unsigned()->nullable();
            $table->string('download_key', 64);
            $table->string('display_name')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('expires_at')->nullable();
        });
        Schema::table('offline_mall_orders', function (Blueprint $table) {
            $table->boolean('is_virtual')->default(0)->after('total_post_taxes');
            $table->date('paid_at')->nullable()->after('shipped_at');
        });
        Schema::table('offline_mall_order_products', function (Blueprint $table) {
            $table->boolean('is_virtual')->default(0)->after('quantity');
        });
        Notification::create([
            'enabled'     => true,
            'code'        => 'offline.mall::product.file_download',
            'name'        => 'Virutal product download links',
            'description' => 'Sent when a customer paid for an order with virtual products',
            'template'    => 'offline.mall::mail.product.file_download',
        ]);
    }

    public function down()
    {
        Schema::table('offline_mall_products', function (Blueprint $table) {
            $table->dropColumn([
                'is_virtual',
                'file_expires_after_days',
                'file_max_download_count',
                'file_session_required',
            ]);
        });
        Schema::table('offline_mall_orders', function (Blueprint $table) {
            $table->dropColumn(['is_virtual', 'paid_at']);
        });
        Schema::table('offline_mall_order_products', function (Blueprint $table) {
            $table->dropColumn(['is_virtual']);
        });
        Schema::dropIfExists('offline_mall_product_files');
        Schema::dropIfExists('offline_mall_product_file_grants');
    }
}
