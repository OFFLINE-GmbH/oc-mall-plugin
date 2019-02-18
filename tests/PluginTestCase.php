<?php

namespace OFFLINE\Mall\Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
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
    }

    public function tearDown()
    {
        parent::tearDown();

        $pluginManager = PluginManager::instance();
        $pluginManager->unregisterAll();
    }
}
