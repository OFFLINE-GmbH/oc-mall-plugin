<?php

declare(strict_types=1);

namespace OFFLINE\Mall;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\View;
use October\Rain\Database\Relations\Relation;
use OFFLINE\Mall\Classes\Registration\BootComponents;
use OFFLINE\Mall\Classes\Registration\BootEvents;
use OFFLINE\Mall\Classes\Registration\BootExtensions;
use OFFLINE\Mall\Classes\Registration\BootMails;
use OFFLINE\Mall\Classes\Registration\BootServiceContainer;
use OFFLINE\Mall\Classes\Registration\BootSettings;
use OFFLINE\Mall\Classes\Registration\BootTwig;
use OFFLINE\Mall\Classes\Registration\BootValidation;
use OFFLINE\Mall\Console\CheckCommand;
use OFFLINE\Mall\Console\IndexCommand;
use OFFLINE\Mall\Console\PurgeCommand;
use OFFLINE\Mall\Console\SeedDataCommand;
use OFFLINE\Mall\Models\CustomField;
use OFFLINE\Mall\Models\CustomFieldOption;
use OFFLINE\Mall\Models\Discount;
use OFFLINE\Mall\Models\ImageSet;
use OFFLINE\Mall\Models\PaymentMethod;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\ServiceOption;
use OFFLINE\Mall\Models\ShippingMethod;
use OFFLINE\Mall\Models\ShippingMethodRate;
use OFFLINE\Mall\Models\Variant;
use System;
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

    /**
     * Required plugin dependencies.
     * @var array
     */
    public $require = [
        'RainLab.User',
        'RainLab.Location',
        'RainLab.Translate',
    ];

    /**
     * Required model morph-map relations, must be registered n the constructor
     * to make them available when the plugin migrations are run.
     * @var array
     */
    protected $relations = [
        Variant::MORPH_KEY            => Variant::class,
        Product::MORPH_KEY            => Product::class,
        ImageSet::MORPH_KEY           => ImageSet::class,
        Discount::MORPH_KEY           => Discount::class,
        CustomField::MORPH_KEY        => CustomField::class,
        PaymentMethod::MORPH_KEY      => PaymentMethod::class,
        ShippingMethod::MORPH_KEY     => ShippingMethod::class,
        CustomFieldOption::MORPH_KEY  => CustomFieldOption::class,
        ShippingMethodRate::MORPH_KEY => ShippingMethodRate::class,
        ServiceOption::MORPH_KEY      => ServiceOption::class,
    ];

    /**
     * Create a new plugin instance.
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        parent::__construct($app);
        Relation::morphMap($this->relations);
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
        if (version_compare(System::VERSION, '3.6', '>=')) {
            $navigation['mall-catalogue']['sideMenu']['mall-reviews']['icon'] = 'icon-star-half';
        }

        return $navigation;
    }
}
