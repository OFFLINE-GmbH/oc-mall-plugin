<?php namespace OFFLINE\Mall\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use System\Classes\SettingsManager;
use Backend\Behaviors\ListController;
use Backend\Behaviors\FormController;

class Taxes extends Controller
{
    public $implement = [ListController::class, FormController::class];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public $requiredPermissions = [
        'offline.mall.manage_taxes',
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('October.System', 'system', 'settings');
        SettingsManager::setContext('OFFLINE.Mall', 'tax_settings');
    }
}
