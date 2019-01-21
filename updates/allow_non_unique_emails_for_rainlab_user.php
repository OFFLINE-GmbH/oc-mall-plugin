<?php namespace OFFLINE\Mall\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AllowNonUniqueEmailsForRainlabUser extends Migration
{
    /**
     * The OFFLINE.Mall plugin allows multiple guest signups
     * with the same email address. Rainlab.User blocks this by
     * using a unique index on the login and email columns.
     */
    public function up()
    {
        Schema::table('users', function ($table) {
            $sm              = Schema::getConnection()->getDoctrineSchemaManager();
            $existingIndexes = array_keys($sm->listTableIndexes('users'));

            $indexesToDelete = ['users_email_unique', 'users_login_unique'];
            foreach ($indexesToDelete as $index) {
                if (in_array($index, $existingIndexes)) {
                    $table->dropUnique($index);
                }
            }
        });
    }

    public function down()
    {
    }
}
