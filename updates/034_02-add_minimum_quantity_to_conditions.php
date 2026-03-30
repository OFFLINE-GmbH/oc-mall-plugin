<?php

namespace OFFLINE\Mall\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class AddMinimumQuantityToConditions extends Migration
{
    public function up()
    {
        Schema::table('offline_mall_discount_conditions', function ($table) {
            $table->unsignedInteger('minimum_quantity')->nullable()->after('product_id');
        });
    }

    public function down()
    {
        Schema::table('offline_mall_discount_conditions', function ($table) {
            $table->dropColumn('minimum_quantity');
        });
    }
}
