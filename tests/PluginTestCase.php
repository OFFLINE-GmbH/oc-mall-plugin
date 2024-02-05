<?php declare(strict_types=1);

namespace OFFLINE\Mall\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use OFFLINE\Mall\Classes\Index\Index;
use OFFLINE\Mall\Classes\Index\Noop;
use OFFLINE\Mall\Models\Currency;

class PluginTestCase extends \PluginTestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * Setup the test environment.
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->artisan('plugin:seed', [
            'namespace' => 'OFFLINE.Mall',
            'class'     => 'OFFLINE\Mall\Updates\Seeders\MallSeeder'
        ]);

        //@todo temporary solution to fix testing
        $this->artisan('plugin:seed', [
            'namespace' => 'OFFLINE.Mall',
            'class'     => 'OFFLINE\Mall\Updates\Seeders\Tables\CustomerGroupTableSeeder'
        ]);

        //@todo temporary solution to fix testing
        $this->artisan('plugin:seed', [
            'namespace' => 'OFFLINE.Mall',
            'class'     => 'OFFLINE\Mall\Updates\Seeders\Tables\CustomerTableSeeder'
        ]);

        //@todo temporary solution to fix testing
        $this->artisan('plugin:seed', [
            'namespace' => 'OFFLINE.Mall',
            'class'     => 'OFFLINE\Mall\Updates\Seeders\Tables\CustomFieldTableSeeder'
        ]);

        //@todo temporary solution to fix testing
        $this->artisan('plugin:seed', [
            'namespace' => 'OFFLINE.Mall',
            'class'     => 'OFFLINE\Mall\Updates\Seeders\Tables\ProductTableSeeder'
        ]);

        // Set CHF as default currency
        //@todo temporary solution to fix testing
        Currency::setActiveCurrency(Currency::where('code', 'CHF')->first());

        app()->bind(Index::class, function() {
            return new Noop();
        });
    }

    /**
     * Tear down the test environment.
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();
    }
}
