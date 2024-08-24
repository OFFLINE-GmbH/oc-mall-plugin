<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Updates;

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use Schema;

class AlterOfflineMallPriceCategories_030_03 extends Migration
{
    /**
     * Install Migration
     *
     * @return void
     */
    public function up()
    {
        Schema::table('offline_mall_price_categories', function (Blueprint $table) {
            $table->boolean('is_enabled')->default(true)->after('sort_order');
            $table->string('title')->default('')->after('name');
        });
    }

    /**
     * Uninstall Migration
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('offline_mall_price_categories', 'is_enabled')) {
            if (method_exists(Schema::class, 'dropColumns')) {
                Schema::dropColumns('offline_mall_price_categories', 'is_enabled');
            } else {
                Schema::table('offline_mall_price_categories', function (Blueprint $table) {
                    $table->dropColumn('is_enabled');
                });
            }
        }
        
        if (Schema::hasColumn('offline_mall_price_categories', 'title')) {
            if (method_exists(Schema::class, 'dropColumns')) {
                Schema::dropColumns('offline_mall_price_categories', 'title');
            } else {
                Schema::table('offline_mall_price_categories', function (Blueprint $table) {
                    $table->dropColumn('title');
                });
            }
        }
    }
};
