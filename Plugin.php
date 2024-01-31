<?php declare(strict_types=1);

namespace OFFLINE\Mall;

use Illuminate\Support\Facades\View;
use Locale;
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
    public $require = ['RainLab.User', 'RainLab.Location', 'RainLab.Translate'];

    use BootEvents;
    use BootExtensions;
    use BootServiceContainer;
    use BootSettings;
    use BootComponents;
    use BootMails;
    use BootValidation;
    use BootTwig;
    use BootRelations;

    public function __construct($app)
    {
        parent::__construct($app);
        // The morph map has to be registered in the constructor so it is available
        // when plugin migrations are run.
        $this->registerRelations();
    }

    public function register()
    {
        $this->registerServices();
        $this->registerTwigEnvironment();
    }

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
}
