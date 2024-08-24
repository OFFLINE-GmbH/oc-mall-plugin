<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Controllers;

use Backend\Behaviors\FormController;
use Backend\Behaviors\ListController;
use Backend\Behaviors\RelationController;
use Backend\Classes\Controller;
use BackendMenu;
use System\Classes\PluginManager;

class CustomerGroups extends Controller
{
    /**
     * Implement behaviors for this controller.
     * @var array
     */
    public $implement = [
        FormController::class,
        ListController::class,
        RelationController::class,
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
     * The configuration file for the relation controller implementation.
     * @var string
     */
    public $relationConfig = 'config_relation.yaml';

    /**
     * Required admin permission to access this page.
     * @var array
     */
    public $requiredPermissions = [
        'offline.mall.manage_customer_groups',
    ];

    /**
     * Construct the controller.
     */
    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('RainLab.User', 'user', 'customer_groups');
    }

    /**
     * Extend relation controller configuration.
     * @param mixed $config
     * @param mixed $field
     * @param mixed $model
     * @return void
     */
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
