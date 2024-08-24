<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Controllers;

use Backend\Behaviors\FormController;
use Backend\Behaviors\ListController;
use Backend\Classes\Controller;
use BackendMenu;
use October\Rain\Database\Builder;
use System\Classes\SettingsManager;

class PriceCategories extends Controller
{
    /**
     * Implement behaviors for this controller.
     * @var array
     */
    public $implement = [
        FormController::class,
        ListController::class,
    ];

    /**
     * The configuration file for the form controller implementation.
     * @var string
     */
    public $formConfig = 'config_form.yaml';

    /**
     * The configuration file for the list controller implementation.
     * @var string
     */
    public $listConfig = 'config_list.yaml';

    /**
     * Required admin permission to access this page.
     * @var array
     */
    public $requiredPermissions = [
        'offline.mall.manage_price_categories',
    ];

    /**
     * Construct the controller.
     */
    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('October.System', 'system', 'settings');
        SettingsManager::setContext('OFFLINE.Mall', 'price_categories_settings');
    }
    
    /**
     * Extend query to show disabled records.
     * @param Builder $query
     * @return void
     */
    public function formExtendQuery(Builder $query)
    {
        $query->withDisabled();
    }
    
    /**
     * Extend query to show disabled records.
     * @param Builder $query
     * @return void
     */
    public function listExtendQuery(Builder $query)
    {
        $query->withDisabled();
    }
}
