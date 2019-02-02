<?php namespace OFFLINE\Mall\Components;

use Auth;
use DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redirect;
use October\Rain\Exception\ValidationException;
use OFFLINE\Mall\Classes\Exceptions\OutOfStockException;
use OFFLINE\Mall\Classes\Queries\VariantByPropertyValuesQuery;
use OFFLINE\Mall\Classes\Traits\CustomFields;
use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\GeneralSettings;
use OFFLINE\Mall\Models\Price;
use OFFLINE\Mall\Models\Product as ProductModel;
use OFFLINE\Mall\Models\Property;
use OFFLINE\Mall\Models\PropertyValue;
use OFFLINE\Mall\Models\Variant;
use Request;
use Session;
use System\Classes\PluginManager;
use Validator;

/**
 * The Product component displays all information of a single Product.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Product extends MallComponent
{
    use CustomFields;
    /**
     * The item to display.
     *
     * @var Product|Variant;
     */
    public $item;
    /**
     * The Product model belonging to the item.
     *
     * @var ProductModel;
     */
    public $product;
    /**
     * The Variants belonging to the ProductModel.
     *
     * @var Collection
     */
    public $variants;
    /**
     * All available PropertyValues of the Variant.
     *
     * @var Collection
     */
    public $variantPropertyValues;
    /**
     * Available Property models.
     *
     * Named "props" to prevent naming conflict with base class.
     *
     * @var Collection
     */
    public $props;
    /**
     * The Variant to display.
     *
     * @var Variant
     */
    public $variant;
    /**
     * The ID of the Variant to display.
     * @var integer
     */
    protected $variantId;
    /**
     * Indicate's that the requested product has not been found.
     * @var bool
     */
    protected $isNotFound;

    /**
     * Component details.
     *
     * @return array
     */
    public function componentDetails()
    {
        return [
            'name'        => 'offline.mall::lang.components.product.details.name',
            'description' => 'offline.mall::lang.components.product.details.description',
        ];
    }

    /**
     * Properties of this component.
     *
     * @return array
     */
    public function defineProperties()
    {
        return [
            'product' => [
                'title'   => 'offline.mall::lang.common.product',
                'default' => ':slug',
                'type'    => 'dropdown',
            ],
            'variant' => [
                'title'   => 'offline.mall::lang.common.variant',
                'default' => ':slug',
                'depends' => ['product'],
                'type'    => 'dropdown',
            ],
        ];
    }

    /**
     * Options array for the products dropdown.
     *
     * @return array
     */
    public function getProductOptions()
    {
        return [':slug' => trans('offline.mall::lang.components.category.properties.use_url')]
            + ProductModel::get()->pluck('name', 'id')->toArray();
    }

    /**
     * Options array for the variants dropdown.
     *
     * @return array
     */
    public function getVariantOptions()
    {
        $product = Request::input('product');
        if ( ! $product || $product === ':slug') {
            return [':slug' => trans('offline.mall::lang.components.category.properties.use_url')];
        }

        return [':slug' => trans('offline.mall::lang.components.category.properties.use_url')]
            + ProductModel::find($product)->variants->pluck('name', 'id')->toArray();
    }

    /**
     * The component is executed.
     *
     * @return string|void
     */
    public function onRun()
    {
        if ($this->isNotFound) {
            return $this->controller->run('404');
        }

        // If a Product model is displayed but it's management method is "variants" the user
        // should be redirected to the first Variant of this Product.
        if ($this->product->inventory_management_method === 'variant' && ! $this->param('variant')) {

            $variant = $this->product->variants->first();
            if ( ! $variant) {
                return $this->controller->run('404');
            }

            $url = $this->controller->pageUrl($this->page->fileName, [
                'slug'    => $this->product->slug,
                'variant' => $variant->hashId,
            ]);

            return redirect()->to($url);
        }

        $this->page->title            = $this->item->meta_title ?? $this->item->name;
        $this->page->meta_description = $this->item->meta_description;
    }

    /**
     * The component is initialized.
     *
     * @return void
     */
    public function init()
    {
        $variantId = $this->decode($this->param('variant'));
        $this->setVar('variantId', $variantId);

        try {
            $this->setVar('item', $this->getItem());
            $this->setVar('variants', $this->getVariants());
        } catch (ModelNotFoundException $e) {
            $this->isNotFound = true;

            return;
        }

        if ( ! $this->product->category) {
            $this->isNotFound = true;
            logger()->error(
                'A product without an existing category has been found.',
                ['id' => $this->item->id, 'name' => $this->item->name]
            );

            return;
        }

        $this->setVar('variantPropertyValues', $this->getPropertyValues());
        $this->setVar('props', $this->getProps());
    }

    /**
     * Add a product to the cart.
     *
     * @return mixed
     * @throws ValidationException
     */
    public function onAddToCart()
    {
        $product = $this->getProduct();
        $variant = null;
        $values  = $this->validateCustomFields(post('fields', []));

        if ($this->variantId !== null) {
            // In case a Variant is added we have to retrieve the model first by the selected props.
            $variant = $this->getVariantByPropertyValues(post('props'));
        }

        $cart     = Cart::byUser(Auth::getUser());
        $quantity = (int)input('quantity', $product->quantity_default ?? 1);
        if ($quantity < 1) {
            throw new ValidationException(['quantity' => trans('offline.mall::lang.common.invalid_quantity')]);
        }
        
        try {
            $cart->addProduct($product, $quantity, $variant, $values);
        } catch (OutOfStockException $e) {
            throw new ValidationException(['stock' => trans('offline.mall::lang.common.stock_limit_reached')]);
        }

        // If the redirect_to_cart option is set to true the user is redirected to the cart.
        if ((bool)GeneralSettings::get('redirect_to_cart', false) === true) {
            $cartPage = GeneralSettings::get('cart_page');

            return Redirect::to($this->controller->pageUrl($cartPage));
        }
    }

    /**
     * The user changed a property of the product.
     *
     * Check the stock for the currentyl selected Variant and return
     * the information back to the user.
     *
     * @return array
     */
    public function onChangeProperty()
    {
        $values  = post('values', []);
        $variant = $this->getVariantByPropertyValues($values);

        $this->page['stock'] = $variant ? $variant->stock : 0;
        $this->page['item']  = $variant ?: $this->getProduct();

        return $this->stockCheckResponse();
    }

    /**
     * Check the stock for the currently selected item.
     *
     * @return array
     * @throws ValidationException
     */
    public function onCheckProductStock()
    {
        $slug = post('slug');
        if ( ! $slug) {
            throw new ValidationException(['Missing input data']);
        }

        $item = $this->getItem();

        $this->page['stock'] = $item ? $item->stock : 0;
        $this->page['item']  = $item;

        return $this->stockCheckResponse();
    }


    /**
     * Return the product's new price.
     *
     * @return array
     */
    public function onChangeConfiguration()
    {
        $fields = $this->mapToCustomFields(post('fields', []));
        if ($fields->count() < 1) {
            return [];
        }

        $values = $this->mapToCustomFieldValues($fields);

        $priceData = $this->getItem()->priceIncludingCustomFieldValues($values);
        $price     = Price::fromArray($priceData);

        $partial = $this->renderPartial($this->alias . '::currentprice', ['price' => $price->string]);

        return [
            '.mall-product__current-price' => $partial,
        ];
    }

    /**
     * Fetch the item to display.
     *
     * This can be either a Product or a Variant depending
     * on the given input values.
     *
     * @return ProductModel|Variant
     */
    protected function getItem()
    {
        $this->product = $this->getProduct();

        // If no Variant is specified as URL parameter the Product
        // model can be returned directly.
        if ( ! $this->param('variant')) {
            return $this->product;
        }

        $variantId    = $this->property('variant');
        $variantModel = Variant::published()->with(['property_values', 'image_sets']);

        // If :slug is set as Variant ID we can fall back to the URL parameter.
        // Otherwise use the Variant the admin as defined as Component property.
        $id = $variantId === ':slug' ? $this->variantId : $variantId;

        return $this->variant = $variantModel->where('product_id', $this->product->id)->findOrFail($id);
    }

    /**
     * Get the ProductModel.
     *
     * @param array|null $with
     *
     * @return ProductModel
     */
    public function getProduct(?array $with = null): ProductModel
    {
        if ($this->product) {
            return $this->product;
        }

        if ($with === null) {
            $with = [
                'variants',
                'variants.property_values',
                'variants.image_sets',
                'image_sets',
                'downloads',
                'category',
                'taxes',
            ];
        }

        $product = $this->property('product');
        $model   = ProductModel::published()->with($with);

        if ($product === ':slug') {
            $method = $this->rainlabTranslateInstalled() ? 'transWhere' : 'where';

            return $model->$method('slug', $this->param('slug'))->firstOrFail();
        }

        return $model->findOrFail($product);
    }

    /**
     * Get all Variants that belong to this ProductModel.
     *
     * @return Collection
     */
    protected function getVariants(): Collection
    {
        // Single Products won't have any Variants.
        if ($this->product->inventory_management_method === 'single' || ! $this->product->group_by_property_id) {
            return collect();
        }

        $variants = $this->product->variants->reject(function (Variant $variant) {
            // Only display "other" Variants, so remove the currently displayed.
            return $variant->id === $this->variantId;
        })->groupBy(function (Variant $variant) {
            return $this->getGroupedPropertyValue($variant);
        });

        if ($this->variant) {
            // Remove the property value of the currently viewed variant.
            $variants->pull($this->getGroupedPropertyValue($this->variant));
        }

        return $variants;
    }

    /**
     * Get the Property this Variant is grouped by.
     *
     * @param Variant $variant
     *
     * @return PropertyValue|object
     */
    protected function getGroupedProperty(Variant $variant)
    {
        if ( ! $variant->product->group_by_property_id) {
            return (object)['value' => 0];
        }

        return $variant->property_values->first(function (PropertyValue $value) use ($variant) {
            return $value->property_id === $variant->product->group_by_property_id;
        });
    }

    /**
     * Get the PropertyValue this Variant is grouped by.
     *
     * @param Variant $variant
     *
     * @return mixed
     */
    protected function getGroupedPropertyValue(Variant $variant)
    {
        $property = $this->getGroupedProperty($variant);

        return \is_array($property->value) ? json_encode($property->value) : $property->value;
    }

    /**
     * Get all Properties of this item.
     *
     * @return Collection
     */
    protected function getProps()
    {
        $valueMap = $this->getValueMap();
        if ($valueMap->count() < 1) {
            return $valueMap;
        }

        return $this->product->category->properties->map(function (Property $property) use ($valueMap) {
            $values = $valueMap->get($property->id);

            return (object)[
                'property' => $property,
                'values'   => optional($values)->unique('value'),
            ];
        })->filter(function ($collection) {
            if ($this->variant && $collection->property->pivot->use_for_variants != true) {
                return false;
            }

            return $collection->values && $collection->values->count() > 0;
        })->keyBy(function ($value) {
            return $value->property->id;
        });
    }

    /**
     * Get a map of all PropertyValues.
     *
     * The key is the property_id, the value is the PropertyValue model.
     *
     * @return Collection
     */
    protected function getValueMap()
    {
        if ( ! $this->variant) {
            return collect([]);
        }

        $groupedValue = $this->getGroupedPropertyValue($this->variant);
        if ($groupedValue === null) {
            return collect([]);
        }

        return PropertyValue
            ::where('product_id', $this->product->id)
            ->where('value', '<>', '')
            ->whereNotNull('value')
            ->when($groupedValue > 0, function ($q) use ($groupedValue) {
                $q->where('value', '<>', $groupedValue);
            })
            ->get()
            ->groupBy('property_id');
    }

    /**
     * Find a Variant by a set of PropertyValue ids.
     *
     * @param $valueIds
     *
     * @return null
     */
    protected function getVariantByPropertyValues($valueIds)
    {
        $ids = collect($valueIds)->map(function ($id) {
            return $this->decode($id);
        });

        $product = $this->getProduct([]);

        $value = (new VariantByPropertyValuesQuery($product, $ids))->query()->first();

        return $value ? $value->variant : null;
    }

    /**
     * Return all PropertyValues of the current Variant.
     *
     * @return Collection
     */
    protected function getPropertyValues()
    {
        if ( ! $this->variant) {
            return collect([]);
        }

        return $this->variant->property_values->keyBy('property_id');
    }

    /**
     * Return the currently available stock information back to the user.
     *
     * @return array
     */
    protected function stockCheckResponse(): array
    {
        $data = [
            'stock' => $this->page['stock'],
            'item'  => $this->page['item'],
        ];

        return [
            '.mall-product__price'       => $this->renderPartial($this->alias . '::price', $data),
            '.mall-product__add-to-cart' => $this->renderPartial($this->alias . '::addtocart', $data),
        ];
    }

    /**
     * Check if RainLab.Translate is available.
     *
     * @return bool
     */
    protected function rainlabTranslateInstalled(): bool
    {
        return PluginManager::instance()->exists('RainLab.Translate');
    }
}
