<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Controllers;

use Backend\Behaviors\FormController;
use Backend\Behaviors\ListController;
use Backend\Classes\Controller;
use BackendMenu;

class PaymentLogs extends Controller
{
    /**
     * Implement behaviors for this controller.
     * @var array
     */
    public $implement = [
        ListController::class,
        FormController::class,
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
     * The configuration file for the filter option within the list controller implementation.
     * @var string
     */
    public $filterConfig = 'config_filter.yaml';

    /**
     * Required admin permission to access this page.
     * @var array
     */
    public $requiredPermissions = [
        'offline.mall.manage_payment_log',
    ];

    /**
     * Construct the controller.
     */
    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('OFFLINE.Mall', 'mall-orders', 'mall-payment-log');
    }

    /**
     * Inject row class name.
     * @param mixed $row
     * @param mixed $definition
     * @return mixed
     */
    public function listInjectRowClass($row, $definition)
    {
        if ($row->failed) {
            return 'negative';
        }
    }
}
