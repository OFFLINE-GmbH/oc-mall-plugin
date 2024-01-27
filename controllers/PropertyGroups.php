<?php declare(strict_types=1);

namespace OFFLINE\Mall\Controllers;

use BackendMenu;
use Flash;
use Backend\Behaviors\FormController;
use Backend\Behaviors\ListController;
use Backend\Behaviors\RelationController;
use Backend\Classes\Controller;
use OFFLINE\Mall\Models\PropertyGroup;

class PropertyGroups extends Controller
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
        'offline.mall.manage_properties',
    ];

    /**
     * Construct the controller.
     */
    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('OFFLINE.Mall', 'mall-catalogue', 'mall-properties');
        $this->addJs('/plugins/offline/mall/assets/backend.js');
    }

    /**
     * Handle relation on reorder
     * @return void
     */
    public function onReorderRelation()
    {
        $records = request()->input('rcd');
        $model   = PropertyGroup::findOrFail($this->params[0]);
        $model->setRelationOrder('properties', $records, range(1, count($records)));

        Flash::success(trans('offline.mall::lang.common.sorting_updated'));
    }
}
