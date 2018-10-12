<?php namespace OFFLINE\Mall\Controllers;

use Backend\Behaviors\FormController;
use Backend\Behaviors\ListController;
use Backend\Behaviors\ReorderController;
use Backend\Classes\Controller;
use BackendMenu;
use System\Classes\SettingsManager;

class OrderState extends Controller
{
    public $implement = [
        ListController::class,
        FormController::class,
        ReorderController::class,
    ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $reorderConfig = 'config_reorder.yaml';

    public $requiredPermissions = [
        'offline.mall.manage_order_states',
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('October.System', 'system', 'settings');
        SettingsManager::setContext('OFFLINE.Mall', 'order_state_settings');
    }

    public function index()
    {
        parent::index();
        $this->addCss('/plugins/offline/mall/assets/backend.css');
    }
}
