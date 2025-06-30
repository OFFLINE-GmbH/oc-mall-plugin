<?php

namespace OFFLINE\Mall\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;

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
            $conn = Schema::getConnection();

            // Laravel <11
            if (method_exists($conn, 'getDoctrineSchemaManager')) {
                $sm              = $conn->getDoctrineSchemaManager();
                $existingIndexes = array_keys($sm->listTableIndexes('users'));
            } else {
                $existingIndexes = Schema::getIndexes('users');
            }

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
