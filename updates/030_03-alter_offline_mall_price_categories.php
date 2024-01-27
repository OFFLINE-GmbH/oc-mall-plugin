<?php declare(strict_types=1);

namespace OFFLINE\Mall\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

return new class extends Migration
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
            Schema::dropColumns('offline_mall_price_categories', 'is_enabled');
        }
    }
};
