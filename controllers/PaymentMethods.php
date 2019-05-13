<?php namespace OFFLINE\Mall\Controllers;

use Backend\Behaviors\FormController;
use Backend\Behaviors\ListController;
use Backend\Behaviors\RelationController;
use Backend\Behaviors\ReorderController;
use Backend\Classes\Controller;
use BackendMenu;
use OFFLINE\Mall\Models\PaymentMethod;
use OFFLINE\Mall\Models\Price;
use System\Classes\SettingsManager;

class PaymentMethods extends Controller
{
    public $implement = [
        ListController::class,
        FormController::class,
        ReorderController::class,
        RelationController::class
    ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $reorderConfig = 'config_reorder.yaml';
    public $relationConfig = 'config_relation.yaml';

    public $requiredPermissions = [
        'offline.mall.settings.manage_payment_methods',
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('October.System', 'system', 'settings');
        SettingsManager::setContext('OFFLINE.Mall', 'payment_method_settings');
    }

    public function formAfterCreate(PaymentMethod $model)
    {
        $this->updatePrices($model);
    }

    public function formAfterUpdate(PaymentMethod $model)
    {
        $this->updatePrices($model);
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
