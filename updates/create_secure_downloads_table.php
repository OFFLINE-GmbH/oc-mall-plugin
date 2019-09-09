<?php namespace Offline\Mall\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

class CreateSecureDownloadsTable extends Migration
{
    public function up()
    {
        Schema::create('offline_mall_secure_downloads', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('product_id');
            $table->text('version');
            $table->text('display_name');
            $table->integer('limite_date')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('offline_mall_secure_downloads');
    }
}
