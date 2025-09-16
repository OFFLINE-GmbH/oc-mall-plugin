<?php
declare(strict_types=1);

namespace OFFLINE\Mall\Updates;

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('offline_mall_product_variants', function (Blueprint $table) {
            $table->text('description_short')->nullable()->change();
            $table->text('description')->nullable()->change();
        });

        Schema::table('offline_mall_products', function (Blueprint $table) {
            $table->text('description_short')->nullable()->change();
            $table->text('description')->nullable()->change();
            $table->text('meta_description')->nullable()->change();
            $table->text('meta_keywords')->nullable()->change();
            $table->longText('additional_descriptions')->nullable()->change();
            $table->text('additional_properties')->nullable()->change();
            $table->text('links')->nullable()->change();
            $table->text('embeds')->nullable()->change();
        });
    }

    public function down()
    {
        // Do nothing.
    }
};
