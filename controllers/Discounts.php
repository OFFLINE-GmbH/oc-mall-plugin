<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Controllers;

use Backend\Behaviors\FormController;
use Backend\Behaviors\ListController;
use Backend\Classes\Controller;
use BackendMenu;
use OFFLINE\Mall\Classes\Database\IsStatesScope;
use OFFLINE\Mall\Models\Discount;
use OFFLINE\Mall\Models\DiscountCondition;
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
        $this->updateConditions($model);
    }

    /**
     * Sync the conditions repeater data to DiscountCondition records.
     * Reads from post('Discount')['_conditions'] — a plain repeater (no useRelation).
     * Uses delete-and-recreate since condition IDs are not referenced externally.
     */
    protected function updateConditions(Discount $model): void
    {
        $items = array_get(post('Discount', []), '_conditions', []);

        // Replace all conditions with the submitted set
        $model->conditions()->delete();

        foreach ($items as $item) {
            $trigger = array_get($item, 'trigger');

            if (empty($trigger)) {
                continue;
            }

            $condition = new DiscountCondition([
                'discount_id'         => $model->id,
                'trigger'             => $trigger,
                'code'                => array_get($item, 'code'),
                'product_id'          => array_get($item, 'product_id') ?: null,
                'minimum_quantity'    => array_get($item, 'minimum_quantity') ?: null,
                'customer_group_id'   => array_get($item, 'customer_group_id') ?: null,
                'payment_method_id'   => array_get($item, 'payment_method_id') ?: null,
                'minimum_total'       => array_get($item, 'minimum_total') ?: null,
                'shipping_method_ids' => array_get($item, 'shipping_method_ids') ?: null,
                'sort_order'          => array_get($item, 'sort_order', 0),
            ]);
            $condition->save();
        }

        $model->unsetRelation('conditions');
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
