<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Updates;

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use Schema;

class CreateOfflineMallProductFileVariantTable_030_08 extends Migration
{
    /**
     * Install Migration
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offline_mall_product_file_variant', function (Blueprint $table) {
            $table->integer('product_file_id')->unsigned();
            $table->integer('variant_id')->unsigned();
            $table->primary(['product_file_id', 'variant_id']);
        });
    }

    /**
     * Uninstall Migration
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('offline_mall_product_file_variant');
    }
};
