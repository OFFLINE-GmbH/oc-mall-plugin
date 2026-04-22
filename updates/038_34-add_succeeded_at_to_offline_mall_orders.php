<?php
declare(strict_types=1);

namespace OFFLINE\Mall\Updates;

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use OFFLINE\Mall\Models\Order;
use Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('offline_mall_orders', function (Blueprint $table) {
            $table->date('succeeded_at')->nullable();
        });
        Order::whereNotNull('paid_at')->update(['succeeded_at' => now()]);
    }

    public function down()
    {
        Schema::table('offline_mall_orders', function (Blueprint $table) {
            $table->dropColumn('succeeded_at');
        });
    }
};
