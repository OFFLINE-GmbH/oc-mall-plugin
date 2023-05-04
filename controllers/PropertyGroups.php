<?php namespace OFFLINE\Mall\Controllers;

use Backend\Behaviors\RelationController;
use Backend\Classes\Controller;
use BackendMenu;
use Backend\Behaviors\ListController;
use Backend\Behaviors\FormController;
use Backend\Behaviors\ReorderController;
use Flash;
use OFFLINE\Mall\Models\PropertyGroup;
use Request;

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

        // Legacy (v1)
        if (!class_exists('System')) {
            $this->addJs('/plugins/offline/mall/assets/Sortable.js');
        }

        $this->addJs('/plugins/offline/mall/assets/backend.js');
    }

    public function onReorderRelation($id)
    {
        $model = PropertyGroup::findOrFail($id);
        if ($model and $fieldName = Request::input('fieldName')) {
            $records = Request::input('rcd');
            $sortKey = array_get($model->getRelationDefinition($fieldName), 'sortKey', 'sort_order');

            $model->setRelationOrder($fieldName, $records, range(1, count($records)), $sortKey);

            Flash::success(trans('offline.mall::lang.common.sorting_updated'));

            $this->initRelation($model, $fieldName);
            return $this->relationRefresh($fieldName);
        }
    }
}
