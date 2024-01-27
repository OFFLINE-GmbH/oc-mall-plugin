<?php declare(strict_types=1);

namespace OFFLINE\Mall\Updates\Seeders\Tables;

use October\Rain\Database\Updates\Seeder;
use OFFLINE\Mall\Models\Currency;

class CurrencyTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * @return void
     */
    public function run()
    {
        Currency::create([
            'is_default' => true,
            'code'       => 'CHF',
            'format'     => '{{ currency.code }} {{ price|number_format(2, ".", "\'") }}',
            'decimals'   => 2,
            'rate'       => 1,
        ]);
        Currency::create([
            'is_default' => false,
            'code'       => 'EUR',
            'format'     => '{{ price|number_format(2, ".", "\'") }}{{ currency.symbol }}',
            'decimals'   => 2,
            'symbol'     => '€',
            'rate'       => 1.14,
        ]);
        Currency::create([
            'is_default' => false,
            'code'       => 'USD',
            'format'     => '{{ currency.symbol }} {{ price|number_format(2, ".", "\'") }}',
            'decimals'   => 2,
            'symbol'     => '$',
            'rate'       => 1.02,
        ]);
        return true;
    }
}
