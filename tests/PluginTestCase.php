<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use OFFLINE\Mall\Classes\Index\Index;
use OFFLINE\Mall\Classes\Index\Noop;
use OFFLINE\Mall\Models\Currency;
use OFFLINE\Mall\Updates\Seeders\MallSeeder;
use OFFLINE\Mall\Updates\Seeders\Tables\CustomerGroupTableSeeder;
use OFFLINE\Mall\Updates\Seeders\Tables\CustomerTableSeeder;
use OFFLINE\Mall\Updates\Seeders\Tables\CustomFieldTableSeeder;
use OFFLINE\Mall\Updates\Seeders\Tables\ProductTableSeeder;
use System;
use System\Classes\PluginManager;

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

        // Seed demo data
        if (version_compare(System::VERSION, '3.0', '<')) {
            $manager = PluginManager::instance();
            $manager->loadPlugins();
            $plugin = $manager->findByIdentifier('offline.mall');
            $manager->registerPlugin($plugin);

            app()->call(MallSeeder::class);
            app()->call(CustomerGroupTableSeeder::class);
            app()->call(CustomerTableSeeder::class);
            app()->call(CustomFieldTableSeeder::class);
            app()->call(ProductTableSeeder::class);
        } else {
            $this->artisan('plugin:seed', [
                'namespace' => 'OFFLINE.Mall',
                'class'     => 'OFFLINE\Mall\Updates\Seeders\MallSeeder',
            ]);
    
            //@todo temporary solution to fix testing
            $this->artisan('plugin:seed', [
                'namespace' => 'OFFLINE.Mall',
                'class'     => 'OFFLINE\Mall\Updates\Seeders\Tables\CustomerGroupTableSeeder',
            ]);
    
            //@todo temporary solution to fix testing
            $this->artisan('plugin:seed', [
                'namespace' => 'OFFLINE.Mall',
                'class'     => 'OFFLINE\Mall\Updates\Seeders\Tables\CustomerTableSeeder',
            ]);
    
            //@todo temporary solution to fix testing
            $this->artisan('plugin:seed', [
                'namespace' => 'OFFLINE.Mall',
                'class'     => 'OFFLINE\Mall\Updates\Seeders\Tables\CustomFieldTableSeeder',
            ]);
    
            //@todo temporary solution to fix testing
            $this->artisan('plugin:seed', [
                'namespace' => 'OFFLINE.Mall',
                'class'     => 'OFFLINE\Mall\Updates\Seeders\Tables\ProductTableSeeder',
            ]);
        }

        // Set CHF as default currency
        Currency::setActiveCurrency(Currency::where('code', 'CHF')->first());

        // Bind No-Op Index
        app()->bind(Index::class, fn () => new Noop());
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
