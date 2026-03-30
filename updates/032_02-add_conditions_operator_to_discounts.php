<?php

namespace OFFLINE\Mall\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class AddConditionsOperatorToDiscounts extends Migration
{
    public function up()
    {
        Schema::table('offline_mall_discounts', function ($table) {
            $table->string('conditions_operator')->default('and')->after('trigger');
        });
    }

    public function down()
    {
        Schema::table('offline_mall_discounts', function ($table) {
            $table->dropColumn('conditions_operator');
        });
    }
}
