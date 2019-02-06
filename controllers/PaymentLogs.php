<?php namespace OFFLINE\Mall\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Backend\Behaviors\ListController;
use Backend\Behaviors\FormController;

class PaymentLogs extends Controller
{
    public $implement = [ListController::class, FormController::class];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $filterConfig = 'config_filter.yaml';

    public $requiredPermissions = [
        'offline.mall.manage_payment_log' 
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('OFFLINE.Mall', 'mall-orders', 'mall-payment-log');
    }

    public function listInjectRowClass($row, $definition)
    {
        if ($row->failed) {
            return 'negative';
        }
    }
}
