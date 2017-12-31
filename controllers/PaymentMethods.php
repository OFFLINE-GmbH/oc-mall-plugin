<?php namespace OFFLINE\Mall\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class PaymentMethods extends Controller
{
    public $implement = [
        'Backend\Behaviors\ListController','Backend\Behaviors\FormController','Backend\Behaviors\ReorderController'    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $reorderConfig = 'config_reorder.yaml';

    public $requiredPermissions = [
        'offline.mall.settings.manage_payment_methods' 
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('OFFLINE.Mall', 'mall', 'mall-payment-methods');
    }
}
