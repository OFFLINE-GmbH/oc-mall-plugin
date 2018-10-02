<?php namespace OFFLINE\Mall\Components;

use Auth;
use DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redirect;
use October\Rain\Exception\ValidationException;
use OFFLINE\Mall\Classes\Exceptions\OutOfStockException;
use OFFLINE\Mall\Classes\Traits\CustomFields;
use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\Product as ProductModel;
use OFFLINE\Mall\Models\Property;
use OFFLINE\Mall\Models\PropertyValue;
use OFFLINE\Mall\Models\Variant;
use Request;
use Session;
use Validator;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Product extends MallComponent
{
    use CustomFields;

    /**
     * @var Product|Variant;
     */
    public $item;
    /**
     * @var ProductModel;
     */
    public $product;
    /**
     * @var Collection
     */
    public $variants;
    /**
     * @var Collection
     */
    public $variantPropertyValues;
    /**
     * Available product properties. Named "props" to prevent
     * naming conflict with base class.
     *
     * @var Collection
     */
    public $props;
    /**
     * @var Variant
     */
    public $variant;
    /**
     * @var integer
     */
    public $variantId;

    public function componentDetails()
    {
        return [
            'name'        => 'offline.mall::lang.components.product.details.name',
            'description' => 'offline.mall::lang.components.product.details.description',
        ];
    }

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

    public function getProductOptions()
    {
        return [':slug' => trans('offline.mall::lang.components.category.properties.use_url')]
            + ProductModel::get()->pluck('name', 'id')->toArray();
    }

    public function getVariantOptions()
    {
        $product = Request::input('product');
        if ( ! $product || $product === ':slug') {
            return [':slug' => trans('offline.mall::lang.components.category.properties.use_url')];
        }

        return [':slug' => trans('offline.mall::lang.components.category.properties.use_url')]
            + ProductModel::find($product)->variants->pluck('name', 'id')->toArray();
    }

    public function onRun()
    {
        try {
            $this->setData();
        } catch (ModelNotFoundException $e) {
            return $this->controller->run('404');
        }

        // If this product is managed by it's variants we redirect to the first available variant.
        if ($this->product->inventory_management_method !== 'single' && ! $this->param('variant')) {

            $variant = $this->product->variants->first();
            if ( ! $variant) {
                $this->controller->run('404');
            }

            $url = $this->controller->pageUrl($this->page->fileName, [
                'slug'    => $this->product->slug,
                'variant' => $variant->hashId,
            ]);

            return Redirect::to($url);
        }

        $this->page->title            = $this->item->meta_title ?? $this->item->name;
        $this->page->meta_description = $this->item->meta_description;
    }

    public function onAddToCart()
    {
        $this->setData();

        $product = $this->getProduct();
        $values  = $this->validateCustomFields(post('fields', []));
        $variant = null;

        if ($this->variantId === null) {
            // We are adding a product
            $hasStock = $product->stock > 0 || $product->allow_out_of_stock_purchases;
        } else {
            // We are adding a product variant
            $variant  = $this->getVariantByPropertyValues(post('props'));
            $hasStock = $variant !== null && ($variant->stock > 0 || $variant->allow_out_of_stock_purchases);
        }

        if ( ! $hasStock) {
            throw new ValidationException(['stock' => trans('offline.mall::lang.common.out_of_stock_short')]);
        }

        $cart     = Cart::byUser(Auth::getUser());
        $quantity = (int)input('quantity', $product->quantity_default ?? 1);
        try {
            $cart->addProduct($product, $quantity, $variant, $values);
        } catch (OutOfStockException $e) {
            throw new ValidationException(['stock' => trans('offline.mall::lang.common.stock_limit_reached')]);
        }
    }

    public function onChangeProperty()
    {
        $values  = post('values', []);
        $variant = $this->getVariantByPropertyValues($values);

        $this->page['stock'] = $variant ? $variant->stock : 0;
        $this->page['item']  = $variant ?: $this->getProduct();

        return $this->stockCheckResponse();
    }

    public function onCheckProductStock()
    {
        $this->setData();

        $slug = post('slug');
        if ( ! $slug) {
            throw new ValidationException(['Missing input data']);
        }

        $item = $this->getItem();

        $this->page['stock'] = $item ? $item->stock : 0;
        $this->page['item']  = $item;

        return $this->stockCheckResponse();
    }

    public function setData()
    {
        $variantId = $this->decode($this->param('variant'));

        $this->setVar('variantId', $variantId);
        $this->setVar('item', $this->getItem());
        $this->setVar('variants', $this->getVariants());
        $this->setVar('variantPropertyValues', $this->getPropertyValues());
        $this->setVar('props', $this->getProps());
    }

    protected function getItem()
    {
        $this->product = $this->getProduct();
        $variant       = $this->property('variant');

        // No Variant was requested via URL
        if ( ! $this->param('variant')) {
            return $this->product;
        }

        $model = Variant::published()->with(['property_values', 'image_sets']);

        if ($variant === ':slug') {
            return $this->variant = $model->where('product_id', $this->product->id)
                                          ->findOrFail($this->variantId);
        }

        return $this->variant = $model->where('product_id', $this->product->id)->findOrFail($variant);
    }

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
                'taxes',
            ];
        }

        $product = $this->property('product');
        $model   = ProductModel::published()->with($with);

        if ($product === ':slug') {
            return $model->transWhere('slug', $this->param('slug'))->firstOrFail();
        }

        return $model->findOrFail($product);
    }

    protected function getVariants(): Collection
    {
        if ($this->product->inventory_management_method === 'single' || ! $this->product->group_by_property_id) {
            return collect();
        }

        $variants = $this->product->variants->reject(function (Variant $variant) {
            // Remove the currently active variant
            return $variant->id === $this->variantId;
        })->groupBy(function (Variant $variant) {
            return $this->getGroupedPropertyValue($variant);
        });

        if ($this->variant) {
            // Remove the property value of the currently viewed variant
            $variants->pull($this->getGroupedPropertyValue($this->variant));
        }

        return $variants;
    }

    protected function getGroupedProperty(Variant $variant)
    {
        if ( ! $variant->product->group_by_property_id) {
            return (object)['value' => 0];
        }

        return $variant->property_values->first(function (PropertyValue $value) use ($variant) {
            return $value->property_id === $variant->product->group_by_property_id;
        });
    }

    protected function getGroupedPropertyValue(Variant $variant)
    {
        $property = $this->getGroupedProperty($variant);

        return \is_array($property->value) ? json_encode($property->value) : $property->value;
    }

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

    protected function getValueMap()
    {
        if ( ! $this->variant) {
            return collect([]);
        }

        $groupedValue = $this->getGroupedPropertyValue($this->variant);
        if ($groupedValue === null) {
            return collect([]);
        }

        return PropertyValue::where('product_id', $this->product->id)
                            ->where('value', '<>', '')
                            ->whereNotNull('value')
                            ->when($groupedValue > 0, function ($q) use ($groupedValue) {
                                $q->where('value', '<>', $groupedValue);
                            })
                            ->get()
                            ->groupBy('property_id');
    }

    protected function getVariantByPropertyValues($valueIds)
    {
        $ids = collect($valueIds)->map(function ($id) {
            return $this->decode($id);
        });

        $product = $this->getProduct([]);

        $query = PropertyValue
            ::leftJoin(
                'offline_mall_product_variants',
                'variant_id', '=', 'offline_mall_product_variants.id'
            )
            ->whereNull('offline_mall_product_variants.deleted_at')
            ->where('offline_mall_product_variants.product_id', $product->id)
            ->select(DB::raw('variant_id, count(*) as matching_attributes'))
            ->groupBy(['variant_id'])
            ->with('variant')
            ->having('matching_attributes', count($ids));

        $query->where(function ($query) use ($ids) {
            PropertyValue::whereIn('id', $ids)
                         ->get(['value', 'property_id'])
                         ->each(function (PropertyValue $propertyValue) use (&$query) {
                             $query->orWhereRaw(
                                 '(property_id, value) = (?, ?)',
                                 [$propertyValue->property_id, $propertyValue->safeValue]
                             );
                         });
        });

        $value = $query->first();

        return $value ? $value->variant : null;
    }

    protected function getPropertyValues()
    {
        if ( ! $this->variant) {
            return collect([]);
        }

        return $this->variant->property_values->keyBy('property_id');
    }

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
}
