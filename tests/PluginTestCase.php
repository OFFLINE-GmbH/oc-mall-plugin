<?php

namespace OFFLINE\Mall\Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use OFFLINE\Mall\Classes\Index\Index;
use OFFLINE\Mall\Classes\Index\Noop;
use System\Classes\PluginManager;

class PluginTestCase extends \PluginTestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();

        $pluginManager = PluginManager::instance();
        $pluginManager->registerAll(true);
        $pluginManager->bootAll(true);

        app()->bind(Index::class, function() {
            return new Noop();
        });
    }

    public function tearDown()
    {
        parent::tearDown();

        $pluginManager = PluginManager::instance();
        $pluginManager->unregisterAll();
    }
}
