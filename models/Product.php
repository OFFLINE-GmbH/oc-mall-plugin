<?php namespace OFFLINE\Mall\Models;

use Cache;
use Cms\Classes\Page;
use DB;
use Model;
use October\Rain\Database\Traits\Nullable;
use October\Rain\Database\Traits\Sluggable;
use October\Rain\Database\Traits\SoftDelete;
use October\Rain\Database\Traits\Validation;
use OFFLINE\Mall\Classes\Index\Index;
use OFFLINE\Mall\Classes\Observers\ProductObserver;
use OFFLINE\Mall\Classes\Traits\CustomFields;
use OFFLINE\Mall\Classes\Traits\HashIds;
use OFFLINE\Mall\Classes\Traits\Images;
use OFFLINE\Mall\Classes\Traits\PriceAccessors;
use OFFLINE\Mall\Classes\Traits\ProductPriceAccessors;
use OFFLINE\Mall\Classes\Traits\PropertyValues;
use OFFLINE\Mall\Classes\Traits\StockAndQuantity;
use OFFLINE\Mall\Classes\Traits\UserSpecificPrice;
use System\Models\File;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Product extends Model
{
    use Validation;
    use SoftDelete;
    use Sluggable;
    use UserSpecificPrice;
    use Images;
    use CustomFields;
    use PropertyValues;
    use HashIds;
    use Nullable;
    use PriceAccessors;
    use ProductPriceAccessors;
    use StockAndQuantity;

    const MORPH_KEY = 'mall.product';

    protected $dates = ['deleted_at'];
    public $jsonable = ['links', 'additional_descriptions', 'additional_properties', 'embeds'];
    public $nullable = ['group_by_property_id'];
    public $implement = ['@RainLab.Translate.Behaviors.TranslatableModel'];
    public $translatable = [
        'name',
        ['slug', 'index' => true],
        'description_short',
        'description',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'links',
        'additional_descriptions',
        'additional_properties',
        'embeds',
    ];
    public $slugs = [
        'slug' => 'name',
    ];
    public $rules = [
        'name'                         => 'required',
        'slug'                         => ['regex:/^[a-z0-9\/\:_\-\*\[\]\+\?\|]*$/i'],
        'weight'                       => 'integer|nullable',
        'stock'                        => 'required_unless:inventory_management_method,variant',
        'published'                    => 'boolean',
        'allow_out_of_stock_purchases' => 'boolean',
    ];
    public $casts = [
        'price_includes_tax'           => 'boolean',
        'allow_out_of_stock_purchases' => 'boolean',
        'weight'                       => 'integer',
        'id'                           => 'integer',
        'stackable'                    => 'boolean',
        'published'                    => 'boolean',
        'stock'                        => 'integer',
        'sales_count'                  => 'integer',
        'shippable'                    => 'boolean',
    ];
    public $fillable = [
        'brand_id',
        'user_defined_id',
        'name',
        'slug',
        'description_short',
        'description',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'weight',
        'inventory_management_method',
        'quantity_default',
        'quantity_min',
        'quantity_max',
        'stock',
        'allow_out_of_stock_purchases',
        'links',
        'stackable',
        'shippable',
        'price_includes_tax',
        'group_by_property_id',
        'published',
        'mpn',
        'gtin',
    ];
    public $table = 'offline_mall_products';
    public $with = ['image_sets', 'prices'];
    public $attachMany = [
        'downloads'      => File::class,
        'initial_images' => File::class,
    ];
    public $belongsTo = [
        'brand'             => Brand::class,
        'group_by_property' => [
            Property::class,
            'key' => 'group_by_property_id',
        ],
    ];
    public $hasManyThrough = [
        'custom_field_options' => [
            CustomFieldOption::class,
            'key'        => 'product_id',
            'through'    => Variant::class,
            'throughKey' => 'custom_field_id',
        ],
    ];
    public $morphMany = [
        'customer_group_prices' => [CustomerGroupPrice::class, 'name' => 'priceable'],
        'additional_prices'     => [Price::class, 'name' => 'priceable'],
    ];
    public $hasMany = [
        'prices'                 => [ProductPrice::class, 'conditions' => 'variant_id is null'],
        'variants'               => Variant::class,
        'cart_products'          => CartProduct::class,
        'image_sets'             => ImageSet::class,
        'property_values'        => PropertyValue::class,
        'reviews'                => Review::class,
        'category_review_totals' => [CategoryReviewTotal::class, 'conditions' => 'variant_id is null'],
    ];
    public $belongsToMany = [
        'categories'      => [
            Category::class,
            'table'    => 'offline_mall_category_product',
            'key'      => 'product_id',
            'otherKey' => 'category_id',
            'pivot'    => ['sort_order'],
        ],
        'custom_fields'   => [
            CustomField::class,
            'table'    => 'offline_mall_product_custom_field',
            'key'      => 'product_id',
            'otherKey' => 'custom_field_id',
        ],
        'accessories'     => [
            Product::class,
            'table'      => 'offline_mall_product_accessory',
            'key'        => 'accessory_id',
            'otherKey'   => 'product_id',
            'conditions' => 'published = 1',
        ],
        'is_accessory_of' => [
            Product::class,
            'table'      => 'offline_mall_product_accessory',
            'key'        => 'product_id',
            'otherKey'   => 'accessory_id',
            'conditions' => 'published = 1',
        ],
        'taxes'           => [
            Tax::class,
            'table'    => 'offline_mall_product_tax',
            'key'      => 'product_id',
            'otherKey' => 'tax_id',
        ],
        'carts'           => [
            Cart::class,
            'table'      => 'offline_mall_cart_products',
            'key'        => 'product_id',
            'otherKey'   => 'cart_id',
            'deleted'    => true,
            'pivot'      => ['id', 'quantity', 'price'],
            'pivotModel' => CartProduct::class,
        ],
        'services'        => [
            Service::class,
            'table'    => 'offline_mall_product_service',
            'key'      => 'product_id',
            'otherKey' => 'service_id',
            'pivot'    => ['required'],
        ],
    ];

    /**
     * Force a re-indexing of this product after save.
     * @var bool
     */
    public $forceReindex = false;

    public function __construct($attributes = [])
    {
        parent::__construct($attributes);
        $this->bindEvent('model.relation.beforeAttach',
            function (string $relationName, array $attachedIdList, array $insertData) {
                $this->forceReindex = true;
            });
        $this->bindEvent('model.relation.beforeDetach', function (string $relationName, array $attachedIdList) {
            $this->forceReindex = true;
        });
    }

    /**
     * Translate url parameters when the user switches the active locale.
     *
     * @param $params
     * @param $oldLocale
     * @param $newLocale
     *
     * @return mixed
     */
    public static function translateParams($params, $oldLocale, $newLocale)
    {
        $newParams = $params;
        foreach ($params as $paramName => $paramValue) {
            if ($paramName !== 'slug') {
                continue;
            }
            $records = self::transWhere($paramName, $paramValue, $oldLocale)->first();
            if ($records) {
                $records->translateContext($newLocale);
                $newParams[$paramName] = $records->$paramName;
            }
        }

        return $newParams;
    }

    public function beforeCreate()
    {
        if ($this->inventory_management_method === 'variant' && $this->stock === null) {
            $this->stock = 0;
        }
    }

    public function afterSave()
    {
        if ($this->forceReindex) {
            $this->forceReindex = false;
            (new ProductObserver(app(Index::class)))->updated($this);
        }
    }

    public function afterDelete()
    {
        $this->prices()->delete();
        $this->additional_prices()->delete();
        $this->variants()->delete();
        $this->property_values()->delete();
        DB::table('offline_mall_product_accessory')->where('product_id', $this->id)->delete();
        DB::table('offline_mall_product_tax')->where('product_id', $this->id)->delete();
        DB::table('offline_mall_cart_products')->where('product_id', $this->id)->delete();
        DB::table('offline_mall_product_custom_field')->where('product_id', $this->id)->delete();
        DB::table('offline_mall_category_product')->where('product_id', $this->id)->delete();
    }

    /**
     * Alias for property_values relationship.
     *
     * This can be useful if a shop uses Products and Variants together.
     * With this alias the all_property_values relation becomes available
     * on Products as it is on Variants.
     */
    public function all_property_values()
    {
        return $this->property_values();
    }

    /**
     * Alias for property_values.
     *
     * @see $this->all_property_values()
     */
    public function getAllPropertyValuesAttribute()
    {
        return $this->property_values;
    }

    /**
     * Return the hashId with a 'product-' prefix.
     */
    public function getPrefixedHashIdAttribute()
    {
        return 'product-' . $this->getHashIdAttribute();
    }

    /**
     * Return the id with a 'product-' prefix.
     */
    public function getPrefixedIdAttribute()
    {
        return 'product-' . $this->id;
    }

    public function getProductHashIdAttribute()
    {
        return $this->getHashIdAttribute();
    }

    public function getProducIdAttribute()
    {
        return $this->id;
    }

    /**
     * This setter makes it easier to set price values
     * in different currencies by providing an array of
     * prices. It is mostly used for unit testing.
     *
     * @param $value
     *
     * @internal
     *
     */
    public function setPriceAttribute($value)
    {
        if ( ! is_array($value)) {
            return;
        }
        foreach ($value as $currency => $price) {
            ProductPrice::updateOrCreate([
                'product_id'  => $this->id,
                'currency_id' => Currency::where('code', $currency)->firstOrFail()->id,
            ], [
                'price' => $price,
            ]);
        }
    }

    public function scopePublished($query)
    {
        return $query->where('published', true);
    }

    public function scopeInCategories($query, $ids)
    {
        if ( ! count($ids)) {
            return $query;
        }

        return $query->whereHas('categories', function ($q) use ($ids) {
            $q->whereIn('category_id', $ids);
        });
    }

    public function getVariantOptionsAttribute()
    {
        return $this->custom_fields()->whereIn('type', ['dropdown', 'color', 'image'])->get();
    }

    /**
     * We are using a simple dropdown for this attribute since the relation
     * widget has some problems with the emptyOption option.
     * @return array
     */
    public function getGroupByPropertyIdOptions()
    {
        return ['' => trans('offline.mall::lang.common.none')]
            + $this->categories->flatMap->properties->filter(function ($q) {
                return $q->pivot->use_for_variants;
            })->pluck('name', 'id')->toArray();
    }

    /**
     * Returns the category specific sort orders of this product.
     */
    public function getSortOrders()
    {
        return Cache::rememberForever(self::sortOrderCacheKey($this->id), function () {
            return \DB::table('offline_mall_category_product')
                      ->where('product_id', $this->id)
                      ->get(['category_id', 'sort_order',])
                      ->pluck('sort_order', 'category_id')
                      ->toArray();
        });
    }

    /**
     * Returns the Cache key to store the sort order.
     *
     * @return string
     */
    public static function sortOrderCacheKey($id)
    {
        return 'category.sort.order.' . $id;
    }

    /**
     * Resolve the item for RainLab.Sitemap and RainLab.Pages plugins.
     *
     * @param $item
     * @param $url
     * @param $theme
     *
     * @return array
     * @throws \Cms\Classes\CmsException
     */
    public static function resolveItem($item, $url, $theme)
    {
        $page    = GeneralSettings::get('product_page', 'product');
        $cmsPage = Page::loadCached($theme, $page);

        if ( ! $cmsPage) {
            return;
        }

        $items = self
            ::published()
            ->where('inventory_management_method', 'single')
            ->get()
            ->map(function (self $product) use ($cmsPage, $page, $url) {
                $pageUrl = $cmsPage->url($page, ['slug' => $product->slug]);

                return [
                    'title'    => $product->name,
                    'url'      => $pageUrl,
                    'mtime'    => $product->updated_at,
                    'isActive' => $pageUrl === $url,
                ];
            })
            ->toArray();

        return [
            'items' => $items,
        ];
    }

    public function filterFields($fields, $context = null)
    {
        if ($context !== 'update') {
            return;
        }

        // If less than properties are available (1 is the null property)
        // we can remove everything that has to do with variants.
        if (count($this->getGroupByPropertyIdOptions()) < 2) {
            $fields->variants->path               = 'variants_unavailable';
            $fields->group_by_property_id->hidden = true;
        }
    }

    public function getInventoryManagementMethodOptions()
    {
        return [
            'single'  => 'offline.mall::lang.variant.method.single',
            'variant' => 'offline.mall::lang.variant.method.variant',
        ];
    }
}
