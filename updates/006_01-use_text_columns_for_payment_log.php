<?php namespace OFFLINE\Mall\Updates;

use Artisan;
use DB;
use October\Rain\Database\Updates\Migration;
use Schema;

class UseTextColumnsForPaymentLog extends Migration
{
    public function up()
    {
        Schema::table('offline_mall_payments_log', function ($table) {
            $table->text('payment_method')->change();
            $table->text('message')->change();
        });
    }

    public function down()
    {
        // Leave the columns. The migration might fail if data gets truncated.
    }
}