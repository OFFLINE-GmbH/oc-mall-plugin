<?php namespace OFFLINE\Mall\Controllers;

use Backend\Behaviors\FormController;
use Backend\Behaviors\ListController;
use Backend\Classes\Controller;
use BackendMenu;
use OFFLINE\Mall\Models\Discount;
use OFFLINE\Mall\Models\Price;

class Discounts extends Controller
{
    public $implement = [
        ListController::class,
        FormController::class,
    ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public $requiredPermissions = [
        'offline.mall.manage_discounts',
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('OFFLINE.Mall', 'mall-orders', 'mall-discounts');
    }

    public function formAfterCreate(Discount $model)
    {
        $this->handleUpdates($model);
    }

    public function formAfterUpdate(Discount $model)
    {
        $this->handleUpdates($model);
    }

    public function handleUpdates(Discount $model)
    {
        $this->updatePrices($model, 'shipping_prices', '_shipping_prices');
        $this->updatePrices($model, 'totals_to_reach', '_totals_to_reach');
        $this->updatePrices($model, 'amounts', '_amounts');
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
