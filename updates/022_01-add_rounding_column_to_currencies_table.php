<?php

namespace OFFLINE\Mall\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class AddRoundingColumnToCurrenciesTable extends Migration
{
    public function up()
    {
        Schema::table('offline_mall_currencies', function ($table) {
            $table->integer('rounding')->nullable();
        });
    }

    public function down()
    {
        Schema::table('offline_mall_currencies', function ($table) {
            $table->dropColumn('rounding');
        });
    }
}
