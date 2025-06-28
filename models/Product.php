<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Models;

use Cache;
use Cms\Classes\Page;
use DB;
use Model;
use October\Rain\Database\Models\DeferredBinding;
use October\Rain\Database\Traits\Nullable;
use October\Rain\Database\Traits\Sluggable;
use October\Rain\Database\Traits\SoftDelete;
use October\Rain\Database\Traits\Validation;
use October\Rain\Support\Collection;
use OFFLINE\Mall\Classes\Index\Index;
use OFFLINE\Mall\Classes\Observers\ProductObserver;
use OFFLINE\Mall\Classes\Traits\CustomFields;
use OFFLINE\Mall\Classes\Traits\FilteredTaxes;
use OFFLINE\Mall\Classes\Traits\HashIds;
use OFFLINE\Mall\Classes\Traits\Images;
use OFFLINE\Mall\Classes\Traits\PDFMaker;
use OFFLINE\Mall\Classes\Traits\PriceAccessors;
use OFFLINE\Mall\Classes\Traits\ProductPriceAccessors;
use OFFLINE\Mall\Classes\Traits\PropertyValues;
use OFFLINE\Mall\Classes\Traits\StockAndQuantity;
use OFFLINE\Mall\Classes\Traits\UserSpecificPrice;
use System\Models\File;

class Product extends Model
{
    use CustomFields;
    use FilteredTaxes;
    use HashIds;
    use Images;
    use Nullable;
    use PDFMaker;
    use PriceAccessors;
    use ProductPriceAccessors;
    use PropertyValues;
    use Sluggable;
    use SoftDelete;
    use StockAndQuantity;
    use UserSpecificPrice;
    use Validation;

    public const MORPH_KEY = 'mall.product';

    /**
     * Implement behaviors for this model.
     * @var array
     */
    public $implement = ['@RainLab.Translate.Behaviors.TranslatableModel'];

    /**
     * The table associated with this model.
     * @var string
     */
    public $table = 'offline_mall_products';

    /**
     * The translatable attributes of this model.
     * @var array
     */
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

    /**
     * The validation rules for the single attributes.
     * @var array
     */
    public $rules = [
        'name'                         => 'required',
        'slug'                         => ['regex:/^[a-z0-9\/\:_\-\*\[\]\+\?\|]*$/i'],
        'weight'                       => 'integer|nullable',
        'height'                       => 'nullable|integer',
        'length'                       => 'nullable|integer',
        'width'                        => 'nullable|integer',
        'stock'                        => 'required_unless:inventory_management_method,variant',
        'published'                    => 'boolean',
        'allow_out_of_stock_purchases' => 'boolean',
        'file_max_download_count'      => 'nullable|integer',
        'file_expires_after_days'      => 'nullable|integer',
        'file_session_required'        => 'nullable|boolean',
    ];

    /**
     * Attribute names that are json encoded and decoded from the database.
     * @var array
     */
    public $jsonable = [
        'links',
        'additional_descriptions',
        'additional_properties',
        'embeds',
    ];

    /**
     * The attributes that are mass assignable.
     * @var array<string>
     */
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
        'length',
        'width',
        'height',
        'inventory_management_method',
        'quantity_default',
        'quantity_min',
        'quantity_max',
        'stock',
        'allow_out_of_stock_purchases',
        'links',
        'stackable',
        'is_virtual',
        'shippable',
        'price_includes_tax',
        'group_by_property_id',
        'published',
        'mpn',
        'gtin',
        'additional_descriptions',
        'additional_properties',
        'file_expires_after_days',
        'file_max_download_count',
        'file_session_required',
    ];

    /**
     * Attributes which should be set to null, when empty.
     * @var array
     */
    public $nullable = [
        'group_by_property_id',
    ];

    /**
     * The attributes that should be cast.
     * @var array
     */
    public $casts = [
        'price_includes_tax'            => 'boolean',
        'allow_out_of_stock_purchases'  => 'boolean',
        'weight'                        => 'integer',
        'height'                        => 'integer',
        'length'                        => 'integer',
        'width'                         => 'integer',
        'id'                            => 'integer',
        'stackable'                     => 'boolean',
        'published'                     => 'boolean',
        'stock'                         => 'integer',
        'sales_count'                   => 'integer',
        'shippable'                     => 'boolean',
        'is_virtual'                    => 'boolean',
        'file_max_download_count'       => 'integer',
        'file_expires_after_days'       => 'integer',
        'file_session_required'         => 'boolean',
        'deleted_at'                    => 'datetime',
    ];

    /**
     * Automatically generate unique URL names for the passed attributes.
     * @var array
     */
    public $slugs = [
        'slug' => 'name',
    ];
    
    /**
     * The accessors to append to the model's array form.
     * @var array
     */
    public $appends = ['hash_id'];
    
    /**
     * The attachMany relationships of this model.
     * @var array
     */
    public $attachMany = [
        'downloads'      => File::class,
        'initial_images' => File::class,
    ];

    /**
     * The belongsTo relationships of this model.
     * @var array
     */
    public $belongsTo = [
        'brand'             => [Brand::class, 'replicate' => false],
        'group_by_property' => [
            Property::class,
            'key'       => 'group_by_property_id',
            'replicate' => false,
        ],
    ];

    /**
     * The hasManyThrough relationships of this model.
     * @var array
     */
    public $hasManyThrough = [
        'custom_field_options' => [
            CustomFieldOption::class,
            'key'        => 'product_id',
            'through'    => Variant::class,
            'throughKey' => 'custom_field_id',
        ],
    ];

    /**
     * The morphMany relationships of this model.
     * @var array
     */
    public $morphMany = [
        'customer_group_prices' => [CustomerGroupPrice::class, 'name' => 'priceable'],
        'additional_prices'     => [Price::class, 'name' => 'priceable'],
    ];

    /**
     * The hasMany relationships of this model.
     * @var array
     */
    public $hasMany = [
        'prices'                 => [ProductPrice::class, 'conditions' => 'variant_id is null'],
        'variants'               => [Variant::class],
        'cart_products'          => [CartProduct::class, 'replicate' => false],
        'order_products'         => [OrderProduct::class, 'replicate' => false],
        'image_sets'             => [ImageSet::class],
        'property_values'        => [PropertyValue::class, 'conditions' => 'variant_id is null'],
        'reviews'                => [Review::class, 'replicate' => false],
        'discounts'              => [Discount::class, 'replicate' => false],
        'category_review_totals' => [CategoryReviewTotal::class, 'conditions' => 'variant_id is null', 'replicate' => false],
        'files'                  => [ProductFile::class],
    ];

    /**
     * The hasOne relationships of this model.
     * @var array
     */
    public $hasOne = [
        'latest_file' => [ProductFile::class, 'order' => 'created_at DESC', 'replicate' => false],
    ];

    /**
     * The belongsToMany relationships of this model.
     * @var array
     */
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
            'replicate'  => false,
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

    /**
     * Cache all filtered countries for this model and this request.
     * @var Collection<Tax>
     */
    private $cachedFilteredTaxes;

    /**
     * Create a new Product modal instance.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->bindEvent('model.relation.beforeAttach', function ($relationName, $attachedIdList, $insertData) {
            $this->forceReindex = true;
        });
        $this->bindEvent('model.relation.beforeDetach', function ($relationName, $attachedIdList) {
            $this->forceReindex = true;
        });

        $this->bindEvent('model.relation.attach', function ($relationName, $attachedIdList, $insertData) {
            if ($relationName === 'categories') {
                foreach ($attachedIdList as $attachedId) {
                    $category = Category::find($attachedId);
                    UniquePropertyValue::updateUsingCategory($category);
                }
            }
        });

        $this->bindEvent('model.relation.detach', function ($relationName, $detachedIdList) {
            if ($relationName === 'categories' && is_array($detachedIdList)) {
                foreach ($detachedIdList as $detachedId) {
                    $category = Category::find($detachedId);
                    UniquePropertyValue::updateUsingCategory($category);
                }
            }
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
        // If the management method goes from single to variant, we need to remove all "variant only" property values
        // from the product. Otherwise we will end up with duplicated values.
        if ($this->getOriginal('inventory_management_method') === 'single' && $this->inventory_management_method === 'variant') {
            $this->forceReindex = true;
            $properties = $this->categories->flatMap->properties->filter(fn ($q) => $q->pivot->use_for_variants)->pluck('id');

            PropertyValue::where('product_id', $this->id)
                ->whereNull('variant_id')
                ->whereIn('property_id', $properties)
                ->delete();
        }

        if ($this->forceReindex) {
            $this->forceReindex = false;
            (new ProductObserver(app(Index::class)))->updated($this);
        }
    }

    public function afterDelete()
    {
        $this->prices()->delete();
        $this->additional_prices()->withDisabled()->delete();
        $this->variants()->delete();
        $this->property_values()->delete();
        DB::table('offline_mall_product_accessory')->where('product_id', $this->id)->delete();
        DB::table('offline_mall_product_tax')->where('product_id', $this->id)->delete();
        DB::table('offline_mall_cart_products')->where('product_id', $this->id)->delete();
        DB::table('offline_mall_product_custom_field')->where('product_id', $this->id)->delete();
        DB::table('offline_mall_category_product')->where('product_id', $this->id)->delete();
        DB::table('offline_mall_wishlist_items')->where('product_id', $this->id)->delete();
    }

    public function duplicate(): self
    {
        $duplicate = $this->replicateWithRelations();
        $duplicate->sales_count = 0;
        $duplicate->name .= ' (copy)';
        $duplicate->slug .= '-copy';
        $duplicate->published = false;
        $duplicate->save();

        return $duplicate;
    }

    /**
     * Handle the form data form the property value form.
     */
    public function handlePropertyValueUpdates()
    {
        $locales = [];

        if (class_exists(\RainLab\Translate\Classes\Locale::class)) {
            $locales = \RainLab\Translate\Classes\Locale::listLocales()->where('is_enabled', true)->all();
        } elseif (class_exists(\RainLab\Translate\Models\Locale::class)) {
            $locales = \RainLab\Translate\Models\Locale::isEnabled()->get();
        }

        $formData = array_wrap(post('PropertyValues', []));

        if (count($formData) < 1) {
            PropertyValue::where('product_id', $this->id)->whereNull('variant_id')->delete();
        }

        $properties     = Property::whereIn('id', array_keys($formData))->get();
        $propertyValues = PropertyValue::where('product_id', $this->id)->whereNull('variant_id')->get();

        foreach ($formData as $id => $value) {
            $property = $properties->find($id);

            $pv = $propertyValues->where('property_id', $id)->first()
                ?? new PropertyValue([
                    'product_id'  => $this->id,
                    'property_id' => $id,
                ]);

            $pv->value = $value;

            foreach ($locales as $locale) {
                $transValue = post(
                    sprintf('RLTranslate.%s.PropertyValues.%d', $locale->code, $id),
                    post(sprintf('PropertyValues.%d', $id)) // fallback
                );
                $transValue = $this->handleTranslatedPropertyValue(
                    $property,
                    $pv,
                    $value,
                    $transValue,
                    $locale->code
                );
                $pv->setAttributeTranslated('value', $transValue, $locale->code);
            }

            // If the value became empty delete it.
            if (($value === null || $value === '') && $pv->exists) {
                $pv->delete();
            } else {
                $pv->save();
            }

            // Transfer any deferred media
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

    public function getProductIdAttribute()
    {
        return $this->id;
    }

    /**
     * Get this product's filtered taxes based on the shipping destination country.
     * @return Collection
     */
    public function getFilteredTaxesAttribute()
    {
        if ($this->cachedFilteredTaxes) {
            return $this->cachedFilteredTaxes;
        }

        return $this->cachedFilteredTaxes = $this->getfilteredTaxes($this->taxes);
    }

    /**
     * This setter makes it easier to set price values
     * in different currencies by providing an array of
     * prices. It is mostly used for unit testing.
     * @internal
     * @param $value
     */
    public function setPriceAttribute($value)
    {
        if (!is_array($value)) {
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
        if (! count($ids)) {
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
            + $this->categories->flatMap->properties->filter(fn ($q) => $q->pivot->use_for_variants)->pluck('name', 'id')->toArray();
    }

    /**
     * Returns the category specific sort orders of this product.
     */
    public function getSortOrders()
    {
        return Cache::rememberForever(self::sortOrderCacheKey($this->id), fn () => DB::table('offline_mall_category_product')
            ->where('product_id', $this->id)
            ->get(['category_id', 'sort_order',])
            ->pluck('sort_order', 'category_id')
            ->toArray());
    }

    /**
     * Returns the Cache key to store the sort order.
     *
     * @param mixed $id
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
     * @param mixed $type
     *
     * @throws \Cms\Classes\CmsException
     * @return array
     */
    public static function resolveItem($item, $url, $theme, $type)
    {
        $page    = GeneralSettings::get('product_page', 'product');
        $cmsPage = Page::loadCached($theme, $page);

        if (! $cmsPage) {
            return;
        }

        $toItem = function (Product|Variant $model) use ($cmsPage, $page, $url) {
            $attrs = ['slug' => $model->slug, 'variant' => ''];

            if ($model instanceof Variant) {
                $attrs['variant'] = $model->hash_id;
            }

            $pageUrl = $cmsPage->url($page, $attrs);

            return [
                'title'    => $model->name,
                'url'      => $pageUrl,
                'mtime'    => $model->updated_at,
                'isActive' => $pageUrl === $url,
            ];
        };

        $result = null;

        if ($type === 'mall-all-products') {
            $data = self::published()->where('inventory_management_method', 'single')->get();

            return [
                'items' => $data->map($toItem),
            ];
        } elseif ($type === 'mall-variant') {
            $result = Variant::published()->find($item->reference);
        } elseif ($type === 'mall-product') {
            $result = self::published()->find($item->reference);
        }

        if (!$result) {
            return [];
        }

        return $toItem($result);
    }

    public function filterFields($fields, $context = null)
    {
        if ($context !== 'update') {
            return;
        }

        if ($this->is_virtual) {
            $this->hideField($fields, 'weight');

            if ($this->files->count() > 0) {
                $this->hideField($fields, 'missing_file_hint');
            }
        } else {
            $this->hideField($fields, 'product_files');
            $this->hideField($fields, 'missing_file_hint');
            $this->hideField($fields, 'product_files_section');
            $this->hideField($fields, 'file_expires_after_days');
            $this->hideField($fields, 'file_max_download_count');
            $this->hideField($fields, 'file_session_required');
        }

        // If less than properties are available (1 is the null property)
        // we can remove everything that has to do with variants.
        if (count($this->getGroupByPropertyIdOptions()) < 2) {
            if ($fields->variants) {
                $fields->variants->path               = 'variants_unavailable';
            }

            if ($fields->group_by_property_id) {
                $fields->group_by_property_id->hidden = true;
            }
        }
    }

    public function getInventoryManagementMethodOptions()
    {
        return [
            'single'  => 'offline.mall::lang.variant.method.single',
            'variant' => 'offline.mall::lang.variant.method.variant',
        ];
    }

    public static function getMenuTypeInfo($type)
    {
        $result = [];

        if ($type === 'mall-product') {
            $references = Product::get()
                ->mapWithKeys(fn (self $product) => [
                    $product->id => [
                        'title' => $product->name,
                    ],
                ])
                ->toArray();
            $result = [
                'references'   => $references,
            ];
        } elseif ($type === 'mall-variant') {
            $references = Variant::get()
                ->mapWithKeys(fn (Variant $variant) => [
                    $variant->id => [
                        'title' => sprintf('%s (%s)', $variant->name, $variant->product->name),
                    ],
                ])
                ->toArray();
            $result = [
                'references'   => $references,
            ];
        }

        return $result;
    }

    /**
     * Hides a field only if it is present. This makes sure
     * the form does not crash if a user programmatically removes
     * a field.
     * @param mixed $fields
     */
    protected function hideField($fields, string $field)
    {
        $isElementHolder = $fields instanceof \October\Rain\Element\ElementHolder;

        if ($isElementHolder && array_key_exists($field, $fields->config)) {
            $fields->config[$field]->hidden = true;
        } elseif (property_exists($fields, $field)) {
            $fields->$field->hidden = true;
        }
    }
}
