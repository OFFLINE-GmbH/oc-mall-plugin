<?php namespace OFFLINE\Mall\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

class AddShortDescriptionFieldToCategory extends Migration
{
    public function up()
    {
        Schema::table('offline_mall_categories', function ($table) {
            $table->string('description_short', 255)->nullable();
        });
    }

    public function down()
    {
        Schema::table('offline_mall_categories', function ($table) {
            $table->dropColumn(['description_short']);
        });
    }
}
