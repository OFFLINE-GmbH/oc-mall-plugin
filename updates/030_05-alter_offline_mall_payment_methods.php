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
        Schema::table('offline_mall_payment_methods', function (Blueprint $table) {
            $table->boolean('is_enabled')->default(true)->after('sort_order');
            $table->boolean('is_default')->default(false)->after('sort_order');
        });
    }

    /**
     * Uninstall Migration
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('offline_mall_payment_methods', 'is_default')) {
            Schema::dropColumns('offline_mall_payment_methods', 'is_default');
        }
        if (Schema::hasColumn('offline_mall_payment_methods', 'is_enabled')) {
            Schema::dropColumns('offline_mall_payment_methods', 'is_enabled');
        }
    }
};
