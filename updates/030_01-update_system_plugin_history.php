<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Updates;

use Exception;
use Illuminate\Support\Facades\DB;
use October\Rain\Database\Updates\Migration;

class UpdateSystemPluginHistory_030_01 extends Migration
{
    /**
     * Install Migration
     *
     * @return void
     */
    public function up()
    {
        $rows = DB::table('system_plugin_history')
            ->select('*')
            ->from('system_plugin_history')
            ->where('code', 'OFFLINE.Mall')
            ->where('type', 'script')
            ->get()
            ->all();

        // Check if upgrade is necessary
        $test = array_filter([...$rows], fn ($val) => preg_match('/^[0-9]{3}\_/', $val->detail) === 0);

        if (empty($test)) {
            return;
        }
        DB::beginTransaction();

        // Loop Rows
        $grp = 0;
        $idx = 0;
        $tag = null;
        $names = [];
        $error = null;

        foreach ($rows as $row) {
            if (array_key_exists($row->detail, $names)) {
                DB::table('system_plugin_history')
                    ->where('id', $row->id)
                    ->update([
                        'detail' => $names[$row->detail],
                    ]);

                continue;
            }

            // Unset missing Mall v2 Migration Files
            if (strpos($row->detail, 'add_product_variants_sort_order.php') !== false) {
                DB::table('system_plugin_history')->where('id', $row->id)->delete();

                continue;
            }

            // Update Filename
            if ($row->version !== $tag) {
                $tag = $row->version;
                $idx = 1;
                $grp++;
            }

            if (preg_match('/[0-9]{3}_[0-9]{2}\-.*/', $row->detail)) {
                $filename = $row->detail;
            } else {
                $filename = substr('00' . $grp, -3) . '_' . substr('0' . $idx, -2) . '-' . $row->detail;
            }
            
            // Check if new file exists
            if (!file_exists(__DIR__ . '/' . $filename)) {
                $error = 'Migration file "' . $filename . '" on path "' . __DIR__ . '" does not exist.';

                continue;
            }

            // Update Migration history
            DB::table('system_plugin_history')
                ->where('id', $row->id)
                ->update([
                    'detail' => $filename,
                ]);
            $names[$row->detail] = $filename;
            $idx++;
        }

        // Commit
        if (empty($error)) {
            DB::commit();
        } else {
            DB::rollBack();

            throw new Exception($error);
        }
    }

    /**
     * Uninstall Migration
     *
     * @return void
     */
    public function down()
    {
    }
};
