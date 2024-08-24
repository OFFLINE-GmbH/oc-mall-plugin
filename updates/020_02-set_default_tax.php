<?php

namespace OFFLINE\Mall\Updates;

use Illuminate\Support\Facades\DB;
use October\Rain\Database\Updates\Migration;

class SetDefaultTax extends Migration
{
    public function up()
    {
        // Version 1.9.0 introduced a default tax. Let's use the first one as default.
        $tax = DB::table('offline_mall_taxes')->first();

        if ($tax) {
            DB::table('offline_mall_taxes')->where('id', $tax->id)->update([
                'is_default'    => true,
            ]);
        }
    }

    public function down()
    {
        // Do nothing.
    }
}
