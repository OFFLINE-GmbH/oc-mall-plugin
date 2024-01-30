<?php declare(strict_types=1);

namespace OFFLINE\Mall\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Backend\Behaviors\ListController;
use Backend\Behaviors\FormController;

class Addresses extends Controller
{
    public $implement = [
        ListController::class,
        FormController::class
    ];
    
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
