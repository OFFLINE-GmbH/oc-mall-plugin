<?php declare(strict_types=1);

namespace OFFLINE\Mall\Controllers;

use BackendMenu;
use Backend\Behaviors\FormController;
use Backend\Behaviors\ListController;
use Backend\Behaviors\RelationController;
use Backend\Classes\Controller;

class CustomFields extends Controller
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
     * Construct the controller.
     */
    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('OFFLINE.Mall', 'mall-catalogue', 'mall-custom-fields');
    }
}
