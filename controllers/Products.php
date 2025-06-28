<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Controllers;

use Backend\Behaviors\FormController;
use Backend\Behaviors\ListController;
use Backend\Behaviors\RelationController;
use Backend\Classes\Controller;
use Backend\Facades\Backend;
use BackendMenu;
use DB;
use Event;
use Flash;
use OFFLINE\Mall\Classes\Database\IsStatesScope;
use OFFLINE\Mall\Classes\Index\Index;
use OFFLINE\Mall\Classes\Observers\ProductObserver;
use OFFLINE\Mall\Classes\Traits\ProductPriceTable;
use OFFLINE\Mall\Models\CustomField;
use OFFLINE\Mall\Models\CustomFieldOption;
use OFFLINE\Mall\Models\ImageSet;
use OFFLINE\Mall\Models\Price;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\ProductFile;
use OFFLINE\Mall\Models\ProductPrice;
use OFFLINE\Mall\Models\Property;
use OFFLINE\Mall\Models\PropertyValue;
use OFFLINE\Mall\Models\Review;
use OFFLINE\Mall\Models\Variant;
use RainLab\Translate\Behaviors\TranslatableModel;

class Products extends Controller
{
    use ProductPriceTable;

    public $turboVisitControl = 'reload';

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
     * The configuration file for the product price table.
     * @var string
     */
    public $productPriceTableConfig = 'config_table.yaml';

    /**
     * Required admin permission to access this page.
     * @var array
     */
    public $requiredPermissions = [
        'offline.mall.manage_products',
    ];

    /**
     * Option Form Widget
     * @var mixed
     */
    protected $optionFormWidget;

    /**
     * Construct the controller.
     */
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

            if (str_contains($this->getAjaxHandler() ?? '', 'PriceTable')) {
                $this->preparePriceTable();
            }
        }
    }

    /**
     * Update View
     * @param mixed $id
     * @return mixed
     */
    public function update($id)
    {
        parent::update($id);

        // Something went wrong if no formModel is available. Proceed with default behavior.
        if (!isset($this->vars['formModel'])) {
            return;
        }

        // If the product has no category something is wrong and needs fixing!
        if (!$this->vars['formModel']->categories) {
            Flash::error(trans('offline.mall::lang.common.action_required'));

            return redirect(Backend::url('offline/mall/products/change_category/' . $id));
        }

        // Strike through all old file versions.
        Event::listen('backend.list.injectRowClass', function ($lists, $record) {
            $latestFile = $this->vars['formModel']->latest_file;

            if (!$latestFile || !$record instanceof ProductFile) {
                return '';
            }

            if (empty(trim($latestFile->version))) {
                return '';
            }

            if ($latestFile->id === $record->id) {
                return '';
            }

            return 'strike safe';
        });
    }

    /**
     * Change Category view
     * @param mixed $id
     * @return mixed
     */
    public function change_category($id)
    {
        $this->pageTitle   = trans('offline.mall::lang.common.action_required');
        $config            = $this->makeConfigFromArray([
            'fields' => [
                'categories' => [
                    'label'           => 'offline.mall::lang.common.category',
                    'nameFrom'        => 'name',
                    'descriptionFrom' => 'description',
                    'span'            => 'auto',
                    'type'            => 'relation',
                ],
            ],
        ]);
        $config->model     = Product::findOrFail($id);
        $config->arrayName = class_basename($config->model);

        $formWidget         = $this->makeWidget('Backend\Widgets\Form', $config);
        $this->vars['form'] = $formWidget;
    }

    /**
     * Change Category Save Handler
     * @return mixed
     */
    public function change_category_onSave()
    {
        $product = Product::findOrFail($this->params[0]);
        $product->categories()->attach(post('Product.categories'));
        $product->save();

        Flash::success(trans('offline.mall::lang.common.saved_changes'));

        return redirect(Backend::url('offline/mall/products/update/' . $this->params[0]));
    }

    /**
     * Save the initial price into the prices table and create an initial image set if images have
     * been uploaded.
     * @param Product $model
     * @return void
     */
    public function formAfterCreate(Product $model)
    {
        $this->updateProductPrices($model, null, '_initial_price');

        if ($model->initial_images->count() > 0) {
            $imageSet = ImageSet::create([
                'name'        => $model->name,
                'is_main_set' => $model->true,
                'product_id'  => $model->id,
            ]);
            DB::table('system_files')
                ->where('field', 'initial_images')
                ->where('attachment_type', Product::MORPH_KEY)
                ->where('attachment_id', $model->id)
                ->update([
                    'field'           => 'images',
                    'attachment_type' => ImageSet::MORPH_KEY,
                    'attachment_id'   => $imageSet->id,
                ]);
        }
    }

    /**
     * Hook after form updated.
     * @param ShippingMethod $model
     * @return void
     */
    public function formAfterUpdate(Product $model)
    {
        $model->handlePropertyValueUpdates();
    }

    /**
     * Undocumented function
     * @return mixed
     */
    public function onCreateOption()
    {
        $data  = $this->optionFormWidget->getSaveData();
        $model = CustomFieldOption::findOrNew(post('edit_id'));
        $model->fill($data);

        if ($model->isClassExtendedWith(TranslatableModel::class) && $translations = post('RLTranslate')) {
            foreach ($translations as $locale => $attributes) {
                foreach ($attributes as $key => $value) {
                    $model->setAttributeTranslated($key, $value, $locale);
                }
            }
        }

        $model->save(null, $this->optionFormWidget->getSessionKey());

        $this->updatePrices($model);

        $field = $this->getCustomFieldModel();
        $field->custom_field_options()->add($model, $this->optionFormWidget->getSessionKey());

        return $this->refreshOptionsList();
    }

    /**
     * Undocumented function
     * @return mixed
     */
    public function onDeleteOption()
    {
        $recordId = post('record_id');
        $model    = CustomFieldOption::find($recordId);
        $field    = $this->getCustomFieldModel();
        $field->custom_field_options()->remove($model, $this->optionFormWidget->getSessionKey());

        return $this->refreshOptionsList();
    }

    /**
     * Undocumented function
     * @return mixed
     */
    public function onApproveReview()
    {
        Review::findOrFail(post('id'))->approve();

        Flash::success(trans('offline.mall::lang.reviews.approved'));

        $this->initRelation(Product::findOrFail($this->params[0]), 'reviews');

        return [
            '#Products-update-RelationController-reviews-view' => $this->relationRenderView('reviews'),
        ];
    }

    /**
     * Undocumented function
     * @return mixed
     */
    public function onLoadCreateOptionForm()
    {
        $this->vars['optionFormWidget'] = $this->optionFormWidget;
        $this->vars['customFieldId']    = post('manage_id');
        $this->vars['type']             = post('type');

        return $this->makePartial('$/offline/mall/controllers/customfields/_option_form.htm');
    }

    /**
     * Undocumented function
     * @return mixed
     */
    public function onLoadEditOptionForm()
    {
        $this->vars['optionFormWidget']    = $this->optionFormWidget;
        $this->vars['customFieldId']       = post('manage_id');
        $this->vars['customFieldOptionId'] = post('option_id');
        $this->vars['type']                = post('type');

        return $this->makePartial('$/offline/mall/controllers/customfields/_option_form.htm');
    }

    /**
     * Undocumented function
     * @param mixed $widget
     * @param mixed $field
     * @param mixed $model
     * @return mixed
     */
    public function relationExtendViewWidget($widget, $field, $model)
    {
        if ($field !== 'variants') {
            return;
        }

        $widget->bindEvent('list.extendQueryBefore', fn ($query) => $query->with('property_values'));
    }

    /**
     * Undocumented function
     * @return mixed
     */
    public function onRelationManageCreate()
    {
        $this->asExtension(RelationController::class)->onRelationManageCreate();

        // Store the pricing information with the custom fields.
        if ($this->relationName === 'custom_fields') {
            $this->updatePrices($this->relationModel, '_prices');
        }

        // Remove the "missing file partial".
        if ($this->relationName === 'files') {
            $parent['#Form-field-Product-missing_file_hint-group'] = '';
        }

        if ($this->relationName === 'variants') {
            $this->updateProductPrices($this->vars['formModel'], $this->relationModel);
            $this->createImageSetFromTempImages($this->relationModel);
            $this->handlePropertyValueUpdates($this->relationModel);

            (new ProductObserver(app(Index::class)))->updated($this->vars['formModel']);
        }

        return $this->asExtension(RelationController::class)->relationRefresh();
    }

    /**
     * Undocumented function
     * @return mixed
     */
    public function onRelationManageUpdate()
    {
        $this->asExtension(RelationController::class)->onRelationManageUpdate();

        // Store the pricing information with the custom fields.
        if ($this->relationName === 'custom_fields') {
            $model = $this->relationModel->find($this->vars['relationManageId']);
            $this->updatePrices($model, '_prices');
        } elseif ($this->relationName === 'variants') {
            $variant = $this->relationModel->find($this->vars['relationManageId']);
            $this->updateProductPrices($this->vars['formModel'], $variant);

            if ($variant->image_set_id === null) {
                $this->createImageSetFromTempImages($variant);
            }

            $this->handlePropertyValueUpdates($variant);

            // Force a re-index of the product
            (new ProductObserver(app(Index::class)))->updated($this->vars['formModel']);
        }

        return $this->asExtension(RelationController::class)->relationRefresh();
    }

    /**
     * Ajax handler to duplicate one or more products.
     * @return mixed
     */
    public function onDuplicateProducts()
    {
        Product::query()
            ->whereIn('id', post('checked', []))
            ->get()
            ->each
            ->duplicate();

        Flash::success(trans('offline.mall::lang.common.duplicated'));

        return $this->asExtension(ListController::class)->listRefresh();
    }

    /**
     * Undocumented function
     * @return mixed
     */
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

    /**
     * Undocumented function
     * @return mixed
     */
    protected function getCustomFieldModel()
    {
        $manageId = post('manage_id');

        return $manageId
            ? CustomField::find($manageId)
            : new CustomField();
    }

    /**
     * Undocumented function
     * @param null|CustomFieldOption $model
     * @return mixed
     */
    protected function createOptionFormWidget(?CustomFieldOption $model = null)
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

    /**
     * Undocumented function
     * @param mixed $field
     * @return mixed
     */
    protected function relationExtendRefreshResults($field)
    {
        if ($field === 'variants') {
            return [
                '#Products-update-RelationController-images-view' => $this->relationRenderView('images'),
            ];
        }
    }

    /**
     * Handle the form data form the property value form.
     * @param Variant $variant
     * @return void
     */
    protected function handlePropertyValueUpdates(Variant $variant)
    {
        $locales = [];

        if (class_exists(\RainLab\Translate\Classes\Locale::class)) {
            $locales = \RainLab\Translate\Classes\Locale::listLocales()->where('is_enabled', true)->all();
        } elseif (class_exists(\RainLab\Translate\Models\Locale::class)) {
            $locales = \RainLab\Translate\Models\Locale::isEnabled()->get();
        }

        $formData = array_wrap(post('VariantPropertyValues', []));

        if (count($formData) < 1) {
            PropertyValue::where('variant_id', $variant->id)->delete();
        }

        $properties     = Property::whereIn('id', array_keys($formData))->get();
        $propertyValues = PropertyValue::where('variant_id', $variant->id)->get();

        foreach ($formData as $id => $value) {
            $property = $properties->find($id);
            $pv       = $propertyValues->where('property_id', $id)->first()
                ?? new PropertyValue([
                    'variant_id'  => $variant->id,
                    'product_id'  => $variant->product_id,
                    'property_id' => $id,
                ]);

            $pv->value = $value;

            foreach ($locales as $locale) {
                $transValue = post(
                    sprintf('RLTranslate.%s.VariantPropertyValues.%d', $locale->code, $id),
                    post(sprintf('VariantPropertyValues.%d', $id)) // fallback
                );
                $transValue = $variant->handleTranslatedPropertyValue(
                    $property,
                    $pv,
                    $value,
                    $transValue,
                    $locale->code
                );
                $pv->setAttributeTranslated('value', $transValue, $locale->code);
            }

            if (($pv->value === null || $pv->value === '') && $pv->exists) {
                $pv->delete();
            } else {
                $pv->save();
            }
        }
    }

    /**
     * Create image-set from temp images.
     * @param Variant $variant
     * @return mixed
     */
    protected function createImageSetFromTempImages(Variant $variant)
    {
        $tempImages = $variant->temp_images()->withDeferred(post('_session_key'))->count();

        if ($tempImages < 1) {
            return;
        } else {
            return DB::transaction(function () use ($variant) {
                $set             = new ImageSet();
                $set->name       = $variant->name;
                $set->product_id = $variant->product_id;
                $set->save();

                $variant->image_set_id = $set->id;
                $variant->save();

                $variant->commitDeferred(post('_session_key'));

                return DB::table('system_files')
                    ->where('attachment_type', Variant::MORPH_KEY)
                    ->where('attachment_id', $variant->id)
                    ->where('field', 'temp_images')
                    ->update([
                        'attachment_type' => ImageSet::MORPH_KEY,
                        'attachment_id'   => $set->id,
                        'field'           => 'images',
                    ]);
            });
        }
    }

    /**
     * Update prices.
     * @param mixed $model
     * @param string $key
     * @return void
     */
    protected function updatePrices($model, $key = 'prices')
    {
        $data = post('MallPrice', []);
        DB::transaction(function () use ($model, $key, $data) {
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
                ], [
                    'price' => $value,
                ]);
            }
        });
    }

    /**
     * Update product prices.
     * @param mixed $product
     * @param mixed $variant
     * @param string $key
     * @return void
     */
    protected function updateProductPrices($product, $variant, $key = '_prices')
    {
        DB::transaction(function () use ($product, $variant, $key) {
            $data = post('MallPrice', []);

            foreach ($data as $currency => $_data) {
                $value = array_get($_data, $key);
                ProductPrice::updateOrCreate([
                    'currency_id' => $currency,
                    'product_id'  => $product->id,
                    'variant_id'  => $variant->id ?? null,
                ], [
                    'price' => $value,
                ]);
            }
        });
    }
}
