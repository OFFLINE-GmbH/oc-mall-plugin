<?php declare(strict_types=1);

namespace OFFLINE\Mall\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use OFFLINE\Mall\Classes\Index\Index;
use OFFLINE\Mall\Classes\Index\Noop;

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

        $this->artisan('plugin:seed', [
            'namespace' => 'OFFLINE.Mall',
            'class'     => 'OFFLINE\Mall\Updates\Seeders\DemoSeeder'
        ]);

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
