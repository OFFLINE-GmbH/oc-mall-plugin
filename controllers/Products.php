<?php namespace OFFLINE\Mall\Controllers;

use Backend\Behaviors\FormController;
use Backend\Behaviors\ListController;
use Backend\Behaviors\RelationController;
use Backend\Classes\Controller;
use Backend\Widgets\Table;
use BackendMenu;
use October\Rain\Database\Models\DeferredBinding;
use OFFLINE\Mall\Models\CurrencySettings;
use OFFLINE\Mall\Models\CustomerGroup;
use OFFLINE\Mall\Models\CustomerGroupPrice;
use OFFLINE\Mall\Models\CustomField;
use OFFLINE\Mall\Models\CustomFieldOption;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\Property;
use OFFLINE\Mall\Models\PropertyValue;
use OFFLINE\Mall\Models\Variant;

class Products extends Controller
{
    public $implement = [
        ListController::class,
        FormController::class,
        RelationController::class,
    ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $relationConfig = 'config_relation.yaml';

    public $requiredPermissions = [
        'offline.mall.manage_products',
    ];

    protected $optionFormWidget;

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('OFFLINE.Mall', 'mall-catalogue', 'mall-products');

        $model                  = post('option_id') ? CustomFieldOption::find(post('option_id')) : null;
        $this->optionFormWidget = $this->createOptionFormWidget($model);
        $this->addCss('/plugins/offline/mall/assets/backend.css');

        if (count($this->params) > 0) {
            // This is pretty hacky but it works. To get the original data from the Variant
            // this session variable is flashed. The Variant model checks for the
            // existence and doesn't inherit the parent product's data if it exists.
            session()->flash('mall.variants.disable-inheritance');
            $this->preparePriceTable();
        }
    }

    public function formAfterUpdate(Product $model)
    {
        $values = post('PropertyValues');
        if ($values === null) {
            return;
        }

        $properties = Property::whereIn('id', array_keys($values))->get();

        foreach ($values as $id => $value) {
            $pv = PropertyValue::firstOrNew([
                'describable_id'   => $model->id,
                'describable_type' => Product::class,
                'property_id'      => $id,
            ]);

            $pv->value = $value;
            $pv->save();

            // Transfer any deferred media
            $property = $properties->find($id);
            if ($property->type === 'image') {
                $media = DeferredBinding::where('master_type', PropertyValue::class)
                                        ->where('master_field', 'image')
                                        ->where('session_key', post('_session_key'))
                                        ->get();

                foreach ($media as $m) {
                    $slave                  = $m->slave_type::find($m->slave_id);
                    $slave->field           = 'image';
                    $slave->attachment_type = PropertyValue::class;
                    $slave->attachment_id   = $pv->id;
                    $slave->save();
                    $m->delete();
                }
            }
        }
    }

    public function onCreateOption()
    {
        $data  = $this->optionFormWidget->getSaveData();
        $model = CustomFieldOption::findOrNew(post('edit_id'));
        $model->fill($data);
        $model->save(null, $this->optionFormWidget->getSessionKey());

        $field = $this->getCustomFieldModel();
        $field->custom_field_options()->add($model, $this->optionFormWidget->getSessionKey());

        return $this->refreshOptionsList();
    }

    public function onDeleteOption()
    {
        $recordId = post('record_id');
        $model    = CustomFieldOption::find($recordId);
        $order    = $this->getCustomFieldModel();
        $order->custom_field_options()->remove($model, $this->optionFormWidget->getSessionKey());

        return $this->refreshOptionsList();
    }

    protected function refreshOptionsList()
    {
        $items = $this->getCustomFieldModel()
                      ->custom_field_options()
                      ->withDeferred($this->optionFormWidget->getSessionKey())
                      ->get();

        $this->vars['items'] = $items;
        $this->vars['type']  = post('type');

        return ['#optionList' => $this->makePartial('$/offline/mall/controllers/customfields/_options_list.htm')];
    }

    protected function getCustomFieldModel()
    {
        $manageId = post('manage_id');
        $order    = $manageId
            ? CustomField::find($manageId)
            : new CustomField();

        return $order;
    }

    public function onLoadCreateOptionForm()
    {
        $this->vars['optionFormWidget'] = $this->optionFormWidget;
        $this->vars['customFieldId']    = post('manage_id');
        $this->vars['type']             = post('type');

        return $this->makePartial('$/offline/mall/controllers/customfields/_option_form.htm');
    }

    public function onLoadEditOptionForm()
    {
        $this->vars['optionFormWidget']    = $this->optionFormWidget;
        $this->vars['customFieldId']       = post('manage_id');
        $this->vars['customFieldOptionId'] = post('option_id');
        $this->vars['type']                = post('type');

        return $this->makePartial('$/offline/mall/controllers/customfields/_option_form.htm');
    }

    protected function createOptionFormWidget(CustomFieldOption $model = null)
    {
        $config                    = $this->makeConfig('$/offline/mall/models/customfieldoption/fields.yaml');
        $config->alias             = 'optionForm';
        $config->arrayName         = 'Option';
        $config->model             = $model ?? new CustomFieldOption();
        $config->model->field_type = post('type');
        $widget                    = $this->makeWidget('Backend\Widgets\Form', $config);
        $widget->bindToController();

        $this->optionFormWidget = $widget;

        return $widget;
    }

    protected function relationExtendRefreshResults($field)
    {
        if ($field !== 'variants') {
            return;
        }

        return [
            '#Products-update-RelationController-images-view' => $this->relationRenderView('images'),
        ];
    }

    public function onLoadPriceTable()
    {
        return $this->makePartial('price_table_modal', ['widget' => $this->vars['pricetable']]);
    }

    protected function preparePriceTable()
    {
        $config = $this->makeConfig('config_table.yaml');

        $customerGroups = CustomerGroup::orderBy('sort_order', 'ASC')->get();
        $customerGroups->each(function (CustomerGroup $group) use ($config) {
            $config->columns[$group->code] = [
                'title' => sprintf(
                    '%s %s',
                    trans('offline.mall::lang.product.price'),
                    $group->name
                ),
            ];
        });
        $this->vars['customerGroups'] = $customerGroups;

        $widget = $this->makeFormWidget(Table::class, $config);
        $widget->bindToController();

        $model     = Product::with([
            'customer_group_prices',
            'variants',
            'variants.customer_group_prices',
        ])->find($this->params[0]);
        $tableData = $model->variants->prepend($model);

        $this->vars['pricetable']      = $widget;
        $this->vars['currencies']      = CurrencySettings::currencies();
        $this->vars['pricetableState'] = $this->processTableData($tableData)->toJson();
    }

    public function onPriceTablePersist()
    {
        $states        = post('state', []);
        $state         = [];
        $firstCurrency = true;
        $priceCols     = $this->vars['customerGroups']
            ->pluck('code')
            ->merge((new Product())->getPriceColumns())
            ->toArray();

        collect($states)->each(function ($records, $currency) use (&$state, &$firstCurrency, $priceCols) {
            foreach ($records as $record) {
                if ($firstCurrency) {
                    $state[$record['id']] = $record;
                    foreach ($priceCols as $priceCol) {
                        $state[$record['id']][$priceCol] = [];
                    }
                }

                foreach ($priceCols as $priceCol) {
                    $state[$record['id']][$priceCol][$currency] = $record[$priceCol];
                }
            }
            $firstCurrency = false;
        });

        foreach ($state as $record) {
            $type  = $record['type'] === 'product' ? Product::class : Variant::class;
            $model = (new $type)->find($record['original_id']);
            $model->forceFill(array_only($record, ['stock', 'price', 'old_price']));
            $model->save();

            foreach ($this->vars['customerGroups'] as $group) {
                if (count(array_filter($record[$group['code']])) < 1) {
                    continue;
                }

                CustomerGroupPrice::updateOrCreate(
                    [
                        'customer_group_id' => $group['id'],
                        'priceable_type'    => $type,
                        'priceable_id'      => $record['original_id'],
                    ],
                    ['price' => $record[$group['code']]]
                );
            }
        }
    }

    protected function processTableData($data)
    {
        return $this->vars['currencies']->map(function ($currency) use ($data) {
            return $data->map(function ($item) use ($currency) {
                $type = $item instanceof Variant ? 'variant' : 'product';

                $data = [
                    'id'          => $type . '-' . $item->id,
                    'original_id' => $item->id,
                    'type'        => $type,
                    'name'        => $item->name,
                    'stock'       => $item->stock,
                    'price'       => $item->priceInCurrency($currency),
                    'old_price'   => $item->oldPriceInCurrency($currency),
                ];

                $prices = $item->customer_group_prices->keyBy('customer_group_id');

                $this->vars['customerGroups']->each(function (CustomerGroup $group) use (&$data, $prices, $currency) {
                    $data[$group->code] = optional($prices->get($group->id))->priceInCurrency($currency) ?? null;
                });

                return $data;
            });
        });
    }
}
