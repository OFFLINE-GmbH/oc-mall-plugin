<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Controllers;

use Backend\Behaviors\FormController;
use Backend\Behaviors\ListController;
use Backend\Behaviors\RelationController;
use Backend\Classes\Controller;
use BackendMenu;
use October\Rain\Database\Builder;
use OFFLINE\Mall\Classes\Database\IsStatesScope;
use OFFLINE\Mall\Models\Price;
use OFFLINE\Mall\Models\ShippingMethod;
use System\Classes\SettingsManager;

class ShippingMethods extends Controller
{
    /**
     * Implement behaviors for this controller.
     * @var array
     */
    public $implement = [
        FormController::class,
        ListController::class,
        RelationController::class,
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
     * The configuration file for the relation controller implementation.
     * @var string
     */
    public $relationConfig = 'config_relation.yaml';

    /**
     * Required admin permission to access this page.
     * @var array
     */
    public $requiredPermissions = [
        'offline.mall.manage_shipping_methods',
    ];

    /**
     * Construct the controller.
     */
    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('October.System', 'system', 'settings');
        SettingsManager::setContext('OFFLINE.Mall', 'shipping_method_settings');
    }
    
    /**
     * Extend query to show disabled records.
     * @param Builder $query
     * @return void
     */
    public function formExtendQuery(Builder $query)
    {
        $query->withDisabled();
    }
    
    /**
     * Extend query to show disabled records.
     * @param Builder $query
     * @return void
     */
    public function listExtendQuery(Builder $query)
    {
        $query->withDisabled();
    }

    /**
     * Hook after form created.
     * @param ShippingMethod $model
     * @return void
     */
    public function formAfterCreate(ShippingMethod $model)
    {
        $this->updatePrices($model);
        $this->updatePrices($model, 'available_below_totals', '_available_below_totals');
        $this->updatePrices($model, 'available_above_totals', '_available_above_totals');
    }

    /**
     * Hook after form updated.
     * @param ShippingMethod $model
     * @return void
     */
    public function formAfterUpdate(ShippingMethod $model)
    {
        $this->updatePrices($model);
        $this->updatePrices($model, 'available_below_totals', '_available_below_totals');
        $this->updatePrices($model, 'available_above_totals', '_available_above_totals');
    }

    /**
     * Undocumented function
     * @return mixed
     */
    public function onRelationManageCreate()
    {
        $parent = parent::onRelationManageCreate();
        $this->checkRelationPriceUpdate();

        return $parent;
    }

    /**
     * Undocumented function
     * @return mixed
     */
    public function onRelationManageUpdate()
    {
        $parent = parent::onRelationManageUpdate();
        $this->checkRelationPriceUpdate();

        return $parent;
    }

    /**
     * Undocumented function
     * @return mixed
     */
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
