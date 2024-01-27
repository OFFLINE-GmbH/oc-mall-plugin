<?php

namespace OFFLINE\Mall\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use OFFLINE\Mall\Classes\Index\Index;
use OFFLINE\Mall\Classes\Index\Noop;

class PluginTestCase extends \PluginTestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->artisan('plugin:seed', [
            'namespace' => 'OFFLINE.Mall',
            'class'     => 'OFFLINE\Mall\Updates\Seeders\MallDatabaseSeeder'
        ]);

        app()->bind(Index::class, function() {
            return new Noop();
        });
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }
}
