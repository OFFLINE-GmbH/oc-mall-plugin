<?php namespace OFFLINE\Mall\Updates;

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use Schema;

class AddDeletedAtColumnToPaymentsLogTable extends Migration
{
    public function up()
    {
        if ( ! Schema::hasTable('offline_mall_payments_log')) {
            return;
        }
        Schema::table('offline_mall_payments_log', function (Blueprint $table) {
            if ( ! Schema::hasColumn('offline_mall_payments_log', 'deleted_at')) {
                $table->timestamp('deleted_at')->nullable();
            }
        });
    }

    public function down()
    {
        if ( ! Schema::hasTable('offline_mall_payments_log')) {
            return;
        }
        Schema::table('offline_mall_payments_log', function (Blueprint $table) {
            if ( Schema::hasColumn('offline_mall_payments_log', 'deleted_at')) {
                $table->dropColumn(['deleted_at']);
            }
        });
    }
}
