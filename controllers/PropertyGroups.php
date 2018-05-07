<?php namespace OFFLINE\Mall\Controllers;

use Backend\Behaviors\RelationController;
use Backend\Classes\Controller;
use BackendMenu;
use Backend\Behaviors\ListController;
use Backend\Behaviors\FormController;
use Backend\Behaviors\ReorderController;
use Flash;
use OFFLINE\Mall\Models\PropertyGroup;

class PropertyGroups extends Controller
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
        'offline.mall.manage_properties',
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('OFFLINE.Mall', 'mall-catalogue', 'mall-properties');
        $this->addJs('/plugins/offline/mall/assets/Sortable.js');
        $this->addJs('/plugins/offline/mall/assets/backend.js');
    }

    public function onReorderRelation()
    {
        $records = request()->input('rcd');
        $model   = PropertyGroup::findOrFail($this->params[0]);
        $model->setRelationOrder('properties', $records, range(1, count($records)));

        Flash::success(trans('offline.mall::lang.common.sorting_updated'));
    }
}
