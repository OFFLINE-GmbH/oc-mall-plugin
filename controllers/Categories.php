<?php namespace OFFLINE\Mall\Controllers;

use Backend\Behaviors\FormController;
use Backend\Behaviors\ListController;
use Backend\Behaviors\RelationController;
use Backend\Behaviors\ReorderController;
use Backend\Classes\Controller;
use BackendMenu;
use Flash;
use OFFLINE\Mall\Models\Category;

class Categories extends Controller
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
        'offline.mall.manage_categories',
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('OFFLINE.Mall', 'mall-catalogue', 'mall-categories');
        $this->addJs('/plugins/offline/mall/assets/Sortable.js');
        $this->addJs('/plugins/offline/mall/assets/backend.js');
    }

    public function onReorderRelation()
    {
        $records = request()->input('rcd');
        $model   = Category::findOrFail($this->params[0]);
        $model->setRelationOrder('property_groups', $records, range(1, count($records)), 'relation_sort_order');

        Flash::success(trans('offline.mall::lang.common.sorting_updated'));
    }

    public function onReorder()
    {
        parent::onReorder();
        (new Category())->purgeCache();
    }
}
