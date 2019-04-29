<?php namespace OFFLINE\Mall\Controllers;

use Backend\Behaviors\FormController;
use Backend\Behaviors\ListController;
use Backend\Behaviors\RelationController;
use Backend\Behaviors\ReorderController;
use Backend\Classes\Controller;
use BackendMenu;
use OFFLINE\Mall\Models\Price;
use OFFLINE\Mall\Models\ShippingMethod;
use System\Classes\SettingsManager;

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

    public $requiredPermissions = [
        'offline.mall.manage_shipping_methods',
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('October.System', 'system', 'settings');
        SettingsManager::setContext('OFFLINE.Mall', 'shipping_method_settings');
    }

    public function formAfterCreate(ShippingMethod $model)
    {
        $this->updatePrices($model);
        $this->updatePrices($model, 'available_below_totals', '_available_below_totals');
        $this->updatePrices($model, 'available_above_totals', '_available_above_totals');
    }

    public function formAfterUpdate(ShippingMethod $model)
    {
        $this->updatePrices($model);
        $this->updatePrices($model, 'available_below_totals', '_available_below_totals');
        $this->updatePrices($model, 'available_above_totals', '_available_above_totals');
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
            if (isset($this->vars['relationManageId'])) {
                $model = $this->relationModel->find($this->vars['relationManageId']);
            } else {
                // In "create" mode, get the latest shipping method rate to update the prices.
                $model = $this->relationModel->newQuery()
                                             ->where('shipping_method_id', $this->params[0])
                                             ->orderByDesc('id')
                                             ->first();
            }
            $this->updatePrices($model);
        }
    }

    protected function updatePrices($model, $field = null, $key = '_prices')
    {
        $data = post('MallPrice', []);
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
                'field'             => $field,
            ], [
                'price' => $value,
            ]);
        }
    }
}
