<?php namespace OFFLINE\Mall\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AllowNonUniqueEmailsForRainlabUser extends Migration
{
    public function up()
    {
        Schema::table('users', function ($table) {
            $table->dropUnique('users_email_unique');
            $table->dropUnique('users_login_unique');
        });
    }

    public function down()
    {
        Schema::table('users', function ($table) {
            $table->string('email')->unique()->change();
            $table->string('username')->unique()->change();
        });
    }
}
