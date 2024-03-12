<?php declare(strict_types=1);

namespace OFFLINE\Mall;

use Illuminate\Support\Facades\View;
use OFFLINE\Mall\Classes\Registration\BootComponents;
use OFFLINE\Mall\Classes\Registration\BootEvents;
use OFFLINE\Mall\Classes\Registration\BootExtensions;
use OFFLINE\Mall\Classes\Registration\BootMails;
use OFFLINE\Mall\Classes\Registration\BootRelations;
use OFFLINE\Mall\Classes\Registration\BootServiceContainer;
use OFFLINE\Mall\Classes\Registration\BootSettings;
use OFFLINE\Mall\Classes\Registration\BootTwig;
use OFFLINE\Mall\Classes\Registration\BootValidation;
use OFFLINE\Mall\Console\CheckCommand;
use OFFLINE\Mall\Console\IndexCommand;
use OFFLINE\Mall\Console\PurgeCommand;
use OFFLINE\Mall\Console\SeedDataCommand;
use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    use BootEvents;
    use BootExtensions;
    use BootServiceContainer;
    use BootSettings;
    use BootComponents;
    use BootMails;
    use BootValidation;
    use BootTwig;
    use BootRelations;

    /**
     * Required plugin dependencies.
     * @var array
     */
    public $require = [
        'RainLab.User', 
        'RainLab.Location', 
        'RainLab.Translate'
    ];

    /**
     * Create a new plugin instance.
     * @return void
     */
    public function __construct($app)
    {
        parent::__construct($app);
        // The morph map has to be registered in the constructor so it is available
        // when plugin migrations are run.
        $this->registerRelations();
    }

    /**
     * Register this plugin.
     * @return void
     */
    public function register()
    {
        $this->registerServices();
        $this->registerTwigEnvironment();
    }

    /**
     * Boot this plugin.
     * @return void
     */
    public function boot()
    {
        $this->registerExtensions();
        $this->registerEvents();
        $this->registerValidationRules();

        $this->registerConsoleCommand('offline.mall.check', CheckCommand::class);
        $this->registerConsoleCommand('offline.mall.index', IndexCommand::class);
        $this->registerConsoleCommand('offline.mall.purge', PurgeCommand::class);
        $this->registerConsoleCommand('offline.mall.seed', SeedDataCommand::class);

        View::share('app_url', config('app.url'));
    }

    /**
     * Register Backend-Navigation items for this plugin.
     * @return array
     */
    public function registerNavigation()
    {
        $navigation = parent::registerNavigation();

        // Icon name has been changed from 'icon-star-half-full' to 'icon-star-half'
        if (version_compare(\System::VERSION, '3.6', '>=')) {
            $navigation['mall-catalogue']['sideMenu']['mall-reviews']['icon'] = 'icon-star-half';
        }

        return $navigation;
    }
}
