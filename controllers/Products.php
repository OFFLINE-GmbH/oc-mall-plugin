<?php namespace OFFLINE\Mall\Controllers;

use Backend\Behaviors\FormController;
use Backend\Behaviors\ListController;
use Backend\Behaviors\RelationController;
use Backend\Classes\Controller;
use Backend\Facades\Backend;
use BackendMenu;
use DB;
use Event;
use Flash;
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
use OFFLINE\Mall\Models\Review;

class Products extends Controller
{
    use ProductPriceTable;

    public $implement = [
        ListController::class,
        FormController::class,
        RelationController::class,
    ];
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $relationConfig = 'config_relation.yaml';
    public $productPriceTableConfig = 'config_table.yaml';
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

            if (str_contains(\Request::header('X-OCTOBER-REQUEST-HANDLER',''), 'PriceTable')) {
                $this->preparePriceTable();
            }
        }
    }

    public function update($id)
    {
        parent::update($id);
        // Something went wrong if no formModel is available. Proceed with default behaviour.
        if ( ! isset($this->vars['formModel'])) {
            return;
        }
        // If the product has no category something is wrong and needs fixing!
        if ( ! $this->vars['formModel']->categories) {
            Flash::error(trans('offline.mall::lang.common.action_required'));

            return redirect(Backend::url('offline/mall/products/change_category/' . $id));
        }

        // Strike through all old file versions.
        Event::listen('backend.list.injectRowClass', function ($lists, $record) {
            $latestFile = $this->vars['formModel']->latest_file;
            if ( ! $latestFile || ! $record instanceof ProductFile) {
                return '';
            }
            if ($latestFile->id === $record->id) {
                return '';
            }

            return 'strike safe';
        });
    }

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

    public function change_category_onSave()
    {
        $product = Product::findOrFail($this->params[0]);
        $product->categories()->attach(post('Product.categories'));
        $product->save();

        Flash::success(trans('offline.mall::lang.common.saved_changes'));

        return redirect(Backend::url('offline/mall/products/update/' . $this->params[0]));
    }

    /**
     * Save the initial price into the prices table and create an
     * initial image set if images have been uploaded.
     *
     * @param Product $model
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

    public function formAfterUpdate(Product $model)
    {
        $model->handlePropertyValueUpdates();
    }

    public function onCreateOption()
    {
        $data  = $this->optionFormWidget->getSaveData();
        $model = CustomFieldOption::findOrNew(post('edit_id'));
        $model->fill($data);
        $model->save(null, $this->optionFormWidget->getSessionKey());

        $this->updatePrices($model);

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

    public function onApproveReview()
    {
        Review::findOrFail(post('id'))->approve();

        Flash::success(trans('offline.mall::lang.reviews.approved'));

        $this->initRelation(Product::findOrFail($this->params[0]), 'reviews');

        return [
            '#Products-update-RelationController-reviews-view' => $this->relationRenderView('reviews'),
        ];
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
        if ($field === 'variants') {
            return [
                '#Products-update-RelationController-images-view' => $this->relationRenderView('images'),
            ];
        }
    }

    public function relationExtendViewWidget($widget, $field, $model)
    {
        if ($field !== 'variants') {
            return;
        }

        $widget->bindEvent('list.extendQueryBefore', function ($query) {
            return $query->with('property_values');
        });
    }

    public function onRelationManageCreate()
    {
        $parent = parent::onRelationManageCreate();

        // Store the pricing information with the custom fields.
        if ($this->relationName === 'custom_fields') {
            $this->updatePrices($this->relationModel, '_prices');
        }

        // Remove the "missing file partial".
        if ($this->relationName === 'files') {
            $parent['#Form-field-Product-missing_file_hint-group'] = '';
        }

        return $parent;
    }

    public function onRelationManageUpdate()
    {
        $parent = parent::onRelationManageUpdate();

        // Store the pricing information with the custom fields.
        if ($this->relationName === 'custom_fields') {
            $model = $this->relationModel->find($this->vars['relationManageId']);
            $this->updatePrices($model, '_prices');
        } elseif ($this->relationName === 'variants') {
            $variant = $this->relationModel->find($this->vars['relationManageId']);
            $this->updateProductPrices($this->vars['formModel'], $variant);

            // Force a re-index of the product
            (new ProductObserver(app(Index::class)))->updated($this->vars['formModel']);
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

    protected function updateProductPrices($product, $variant, $key = '_prices')
    {
        \DB::transaction(function () use ($product, $variant, $key) {
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
