<?php namespace OFFLINE\Mall\Updates;

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use Schema;

class AddVirtualProductsSupport extends Migration
{
    public function up()
    {
        Schema::table('offline_mall_products', function (Blueprint $table) {
            $table->boolean('is_virtual')->default(false);
        });

        Schema::create('offline_mall_product_files', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('product_id')->unsigned();
            $table->string('version');
            $table->string('display_name');
            $table->integer('download_count')->default(0);
            $table->integer('expires_after_days')->nullable();
            $table->integer('max_download_count')->nullable();
            $table->boolean('session_required')->default(0);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::table('offline_mall_products', function (Blueprint $table) {
            $table->dropColumn(['is_virtual']);
        });
        Schema::dropIfExists('offline_mall_product_files');
    }
}
