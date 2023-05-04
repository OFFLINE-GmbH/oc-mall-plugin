<?php namespace OFFLINE\Mall\Controllers;

use Backend\Behaviors\FormController;
use Backend\Behaviors\ListController;
use Backend\Behaviors\RelationController;
use Backend\Classes\Controller;
use BackendMenu;
use Flash;
use OFFLINE\Mall\Models\Price;
use OFFLINE\Mall\Models\Service;
use Request;

class Services extends Controller
{
    public $implement = [ListController::class, FormController::class, RelationController::class];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $relationConfig = 'config_relation.yaml';

    public $requiredPermissions = [
        'offline.mall.manage_services',
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('OFFLINE.Mall', 'mall-catalogue', 'mall-services');

        // Legacy (v1)
        if (!class_exists('System')) {
            $this->addJs('/plugins/offline/mall/assets/Sortable.js');
        }

        $this->addJs('/plugins/offline/mall/assets/backend.js');
    }

    public function onReorderRelation($id)
    {
        $model = Service::findOrFail($id);
        if ($model && $fieldName = Request::input('fieldName')) {
            $records = Request::input('rcd');
            $sortKey = array_get($model->getRelationDefinition($fieldName), 'sortKey', 'sort_order');

            $model->setRelationOrder($fieldName, $records, range(1, count($records)), $sortKey);

            Flash::success(trans('offline.mall::lang.common.sorting_updated'));

            $this->initRelation($model, $fieldName);
            return $this->relationRefresh($fieldName);
        }
    }

    public function onRelationManageCreate()
    {
        $parent = parent::onRelationManageCreate();

        // Store the pricing information with the custom fields.
        if ($this->relationName === 'options') {
            $this->updatePrices($this->relationModel, '_prices');
        }

        return $parent;
    }

    public function onRelationManageUpdate()
    {
        $parent = parent::onRelationManageUpdate();

        // Store the pricing information with the custom fields.
        if ($this->relationName === 'options') {
            $model = $this->relationModel->find($this->vars['relationManageId']);
            $this->updatePrices($model, '_prices');
        }

        return $parent;
    }

    protected function updatePrices($model, $key = 'prices')
    {
        $data = post('MallPrice', []);
        \DB::transaction(function () use ($model, $key, $data) {
            foreach ($data as $currency => $_data) {
                $value = array_get($_data, $key);
                if ($value === '') {
                    $value = null;
                }

                Price::updateOrCreate([
                    'price_category_id' => null,
                    'priceable_id'      => $model->id,
                    'priceable_type'    => $model::MORPH_KEY,
                    'currency_id'       => $currency,
                ], [
                    'price' => $value,
                ]);
            }
        });
    }
}
