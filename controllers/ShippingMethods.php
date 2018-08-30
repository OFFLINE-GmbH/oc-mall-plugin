<?php namespace OFFLINE\Mall\Controllers;

use Backend\Behaviors\FormController;
use Backend\Behaviors\ListController;
use Backend\Behaviors\RelationController;
use Backend\Behaviors\ReorderController;
use Backend\Classes\Controller;
use BackendMenu;
use OFFLINE\Mall\Models\Price;
use OFFLINE\Mall\Models\ShippingMethod;

class ShippingMethods extends Controller
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

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('OFFLINE.Mall', 'mall-orders', 'mall-shipping-methods');
    }

    public function formAfterCreate(ShippingMethod $model)
    {
        $this->updatePrices($model);
        $this->updatePrices($model, 'available_below_total', '_available_below_total');
        $this->updatePrices($model, 'available_above_total', '_available_above_total');
    }

    public function formAfterUpdate(ShippingMethod $model)
    {
        $this->updatePrices($model);
        $this->updatePrices($model, 'available_below_total', '_available_below_total');
        $this->updatePrices($model, 'available_above_total', '_available_above_total');
    }

    public function onRelationManageUpdate()
    {
        $parent = parent::onRelationManageUpdate();

        $this->checkRelationPriceUpdate();

        return $parent;
    }

    public function onRelationManageCreate()
    {
        $parent = parent::onRelationManageCreate();

        $this->checkRelationPriceUpdate();

        return $parent;
    }

    protected function checkRelationPriceUpdate()
    {
        if ($this->relationName === 'rates') {
            $model = $this->relationModel->find($this->vars['relationManageId']);
            $this->updatePrices($model);
        }
    }

    protected function updatePrices($model, $field = null, $key = '_prices')
    {
        $data = post('MallPrice');
        foreach ($data as $currency => $_data) {
            $value = array_get($_data, $key);
            if ($value === "") {
                $value = null;
            }
            Price::updateOrCreate([
                'price_category_id' => null,
                'priceable_id'      => $model->id,
                'priceable_type'    => $model::MORPH_KEY,
                'currency_id'       => $currency,
                'field'             => $field,
            ], [
                'price' => $value,
            ]);
        }
    }
}
