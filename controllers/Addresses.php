<?php namespace OFFLINE\Mall\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Addresses extends Controller
{
    public $implement = [        'Backend\Behaviors\ListController',        'Backend\Behaviors\FormController'    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public $requiredPermissions = [
        'offline.mall.manage_customer_addresses' 
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('RainLab.User', 'user', 'customer_addresses');
    }
}
