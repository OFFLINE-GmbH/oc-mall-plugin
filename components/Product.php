<?php namespace OFFLINE\Mall\Components;

use Auth;
use DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redirect;
use October\Rain\Exception\ValidationException;
use OFFLINE\Mall\Classes\Exceptions\OutOfStockException;
use OFFLINE\Mall\Classes\Traits\CustomFields;
use OFFLINE\Mall\Models\Cart;
use OFFLINE\Mall\Models\CustomField;
use OFFLINE\Mall\Models\CustomFieldValue;
use OFFLINE\Mall\Models\Product as ProductModel;
use OFFLINE\Mall\Models\Property;
use OFFLINE\Mall\Models\PropertyValue;
use OFFLINE\Mall\Models\Variant;
use Request;
use Session;
use Validator;

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
        $this->setData();

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

        // We are adding a product
        if ($this->variantId === null) {
            $hasStock = $product->stock > 0 || $product->allow_out_of_stock_purchases;
        } else {
            // We are adding a product variant
            $variant  = $this->getVariantByPropertyValues(post('props'));
            $hasStock = $variant !== null;
        }

        if ( ! $hasStock) {
            throw new ValidationException(['stock' => trans('offline.mall::lang.common.out_of_stock_short')]);
        }

        $cart = Cart::byUser(Auth::getUser());
        try {
            $cart->addProduct($product, 1, $variant, $values);
        } catch (OutOfStockException $e) {
            throw new ValidationException(['stock' => trans('offline.mall::lang.common.stock_limit_reached')]);
        }
    }

    public function onChangeProperty()
    {
        $valueIds = post('values');
        if ( ! $valueIds) {
            throw new ValidationException(['Missing input data']);
        }

        $variant = $this->getVariantByPropertyValues(post('values'));

        $this->page['stock'] = $variant ? $variant->stock : 0;
        $this->page['item']  = $variant ? $variant : $this->getProduct();
    }

    public function onCheckProductStock()
    {
        $slug = post('slug');
        if ( ! $slug) {
            throw new ValidationException(['Missing input data']);
        }

        $product = ProductModel::published()->whereSlug($slug)->firstOrFail();

        $this->page['stock'] = $product ? $product->stock : 0;
        $this->page['item']  = $product;
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

        $model = Variant::published()->with(['property_values', 'images', 'main_image']);

        if ($variant === ':slug') {
            return $this->variant = $model->where('product_id', $this->product->id)
                                          ->findOrFail($this->variantId);
        }

        return $this->variant = $model->where('product_id', $this->product->id)->findOrFail($variant);
    }

    public function getProduct(): ProductModel
    {
        if ($this->product) {
            return $this->product;
        }

        $product = $this->property('product');
        $model   = ProductModel::published()->with([
            'variants',
            'variants.property_values',
            'variants.images',
            'variants.main_image',
            'images',
            'downloads',
            'taxes',
        ]);

        if ($product === ':slug') {
            return $model->where('slug', $this->param('slug'))->firstOrFail();
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
            return $this->getGroupedProperty($variant)->value;
        });

        if ($this->variant) {
            // Remove the property value of the currently viewed variant
            $variants->pull($this->getGroupedProperty($this->variant)->value);
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

        $groupedValue = $this->getGroupedProperty($this->variant)->value;
        if ($groupedValue === null) {
            return collect([]);
        }

        $ids = PropertyValue::where('value', $groupedValue)
                            ->where('describable_type', Variant::class)
                            ->get(['describable_id'])
                            ->pluck('describable_id')
                            ->unique();

        return PropertyValue::whereIn('describable_id', $ids)
                            ->where('describable_type', Variant::class)
                            ->where('value', '<>', '')
                            ->whereNotNull('value')
                            ->get()
                            ->groupBy('property_id');
    }

    protected function getVariantByPropertyValues($valueIds)
    {
        $ids = collect($valueIds)->map(function ($id) {
            return $this->decode($id);
        });

        $values = PropertyValue::whereIn('id', $ids)->get(['value'])->pluck('value');

        $variant = PropertyValue::whereIn('value', $values)
                                ->leftJoin(
                                    'offline_mall_product_variants',
                                    'describable_id', '=', 'offline_mall_product_variants.id'
                                )
                                ->where('describable_type', Variant::class)
                                ->whereNull('offline_mall_product_variants.deleted_at')
                                ->select(DB::raw('*, count(*) as matching_attributes'))
                                ->groupBy('describable_id')
                                ->having('matching_attributes', $values->count())
                                ->first();

        return $variant ? $variant->describable : null;
    }

    protected function getPropertyValues()
    {
        if ( ! $this->variant) {
            return collect([]);
        }

        return $this->variant->property_values->keyBy('property_id');
    }
}
