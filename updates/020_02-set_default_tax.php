<?php namespace OFFLINE\Mall\Updates;

use October\Rain\Database\Updates\Migration;
use OFFLINE\Mall\Models\Tax;
use Schema;

class SetDefaultTax extends Migration
{
    public function up()
    {
        // Version 1.9.0 introduced a default tax. Let's use the first one as default.
        $tax = Tax::first();
        if ($tax) {
            $tax->is_default = true;
            $tax->save();
        }
    }

    public function down()
    {
        // Do nothing.
    }
}
