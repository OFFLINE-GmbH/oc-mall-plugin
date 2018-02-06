<?php namespace OFFLINE\Mall\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class OrderState extends Controller
{
    public $implement = [
        'Backend\Behaviors\ListController','Backend\Behaviors\FormController','Backend\Behaviors\ReorderController'    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $reorderConfig = 'config_reorder.yaml';

    public $requiredPermissions = [
        'offline.mall.manage_orders' 
    ];

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        parent::index();
        $this->addCss('/plugins/offline/mall/assets/backend.css');
    }
}
