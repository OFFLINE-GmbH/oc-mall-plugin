<?php namespace OFFLINE\Mall\Controllers;

use Backend\Behaviors\RelationController;
use Backend\Classes\Controller;
use BackendMenu;
use Backend\Behaviors\ListController;
use Backend\Behaviors\FormController;
use Backend\Behaviors\ReorderController;

use System\Classes\PluginManager;

class CustomerGroups extends Controller
{
    public $implement = [
        ListController::class,
        FormController::class,
        ReorderController::class,
        RelationController::class,
    ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $reorderConfig = 'config_reorder.yaml';
    public $relationConfig = 'config_relation.yaml';

    public $requiredPermissions = [
        'offline.mall.manage_customer_groups',
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('RainLab.User', 'user', 'customer_groups');
    }

    public function relationExtendConfig($config, $field, $model)
    {
        // Keep compatibility with Winter CMS
        if ($field === 'users' && PluginManager::instance()->hasPlugin('Winter.Location')) {
            $config->view['list']   = '$/winter/user/models/user/columns.yaml';
            $config->manage['list'] = '$/winter/user/models/user/columns.yaml';
            $config->manage['form'] = '$/winter/user/models/user/fields.yaml';
        }
    }
}
