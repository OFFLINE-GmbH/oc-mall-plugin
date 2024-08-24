<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Controllers;

use Backend\Behaviors\FormController;
use Backend\Behaviors\ListController;
use Backend\Classes\Controller;
use BackendMenu;
use OFFLINE\Mall\Classes\Database\IsStatesScope;
use OFFLINE\Mall\Models\Discount;
use OFFLINE\Mall\Models\Price;

class Discounts extends Controller
{
    /**
     * Implement behaviors for this controller.
     * @var array
     */
    public $implement = [
        FormController::class,
        ListController::class,
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
     * Required admin permission to access this page.
     * @var array
     */
    public $requiredPermissions = [
        'offline.mall.manage_discounts',
    ];

    /**
     * Construct the controller.
     */
    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('OFFLINE.Mall', 'mall-orders', 'mall-discounts');
    }

    /**
     * Hook after form created.
     * @param Discount $model
     * @return void
     */
    public function formAfterCreate(Discount $model)
    {
        $this->handleUpdates($model);
    }

    /**
     * Hook after form updated.
     * @param Discount $model
     * @return void
     */
    public function formAfterUpdate(Discount $model)
    {
        $this->handleUpdates($model);
    }

    /**
     * Handle form updates.
     * @param Discount $model
     * @return void
     */
    public function handleUpdates(Discount $model)
    {
        $this->updatePrices($model, 'shipping_prices', '_shipping_prices');
        $this->updatePrices($model, 'totals_to_reach', '_totals_to_reach');
        $this->updatePrices($model, 'amounts', '_amounts');
    }

    /**
     * Update Prices
     * @param mixed $model
     * @param mixed $field
     * @param string $key
     * @return void
     */
    protected function updatePrices($model, $field = null, $key = '_prices')
    {
        $data = post('MallPrice', []);

        foreach ($data as $currency => $_data) {
            $value = array_get($_data, $key);

            if ($value === '') {
                $value = null;
            }
            Price::withoutGlobalScope(new IsStatesScope())->updateOrCreate([
                'price_category_id' => null,
                'priceable_id' => $model->id,
                'priceable_type' => $model::MORPH_KEY,
                'currency_id' => $currency,
                'field' => $field,
            ], [
                'price' => $value,
            ]);
        }
    }
}
