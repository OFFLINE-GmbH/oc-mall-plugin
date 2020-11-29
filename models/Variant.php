<?php namespace OFFLINE\Mall\Models;

use Cms\Classes\Page;
use DB;
use Html;
use Illuminate\Support\Collection;
use Model;
use October\Rain\Database\Traits\Nullable;
use October\Rain\Database\Traits\SoftDelete;
use October\Rain\Database\Traits\Validation;
use OFFLINE\Mall\Classes\Traits\CustomFields;
use OFFLINE\Mall\Classes\Traits\HashIds;
use OFFLINE\Mall\Classes\Traits\Images;
use OFFLINE\Mall\Classes\Traits\PriceAccessors;
use OFFLINE\Mall\Classes\Traits\ProductPriceAccessors;
use OFFLINE\Mall\Classes\Traits\PropertyValues;
use OFFLINE\Mall\Classes\Traits\StockAndQuantity;
use OFFLINE\Mall\Classes\Traits\UserSpecificPrice;
use RainLab\Translate\Models\Locale;
use System\Models\File;

class Variant extends Model
{
    use Validation;
    use SoftDelete;
    use Images;
    use HashIds;
    use CustomFields;
    use UserSpecificPrice;
    use Nullable;
    use PriceAccessors;
    use ProductPriceAccessors;
    use PropertyValues;
    use StockAndQuantity;

    const MORPH_KEY = 'mall.variant';

    public $slugs = [];
    public $nullable = ['image_set_id'];
    public $table = 'offline_mall_product_variants';
    public $dates = ['deleted_at'];
    public $with = ['product.additional_prices', 'image_sets', 'prices', 'additional_prices'];
    public $implement = ['@RainLab.Translate.Behaviors.TranslatableModel'];
    public $translatable = [
        'name',
        'description_short',
        'description',
    ];
    public $casts = [
        'published'                    => 'boolean',
        'allow_out_of_stock_purchases' => 'boolean',
        'id'                           => 'integer',
        'stock'                        => 'integer',
        'sales_count'                  => 'integer',
        'weight'                       => 'integer',
        'height'                       => 'integer',
        'length'                       => 'integer',
        'width'                        => 'integer',
    ];
    public $rules = [
        'name'                         => 'required',
        'product_id'                   => 'required',
        'stock'                        => 'required|integer',
        'weight'                       => 'nullable|integer',
        'height'                       => 'nullable|integer',
        'length'                       => 'nullable|integer',
        'width'                        => 'nullable|integer',
        'published'                    => 'boolean',
        'allow_out_of_stock_purchases' => 'boolean',
    ];
    public $attachMany = [
        'temp_images' => File::class,
        'downloads'   => File::class,
    ];
    public $belongsTo = [
        'product'    => Product::class,
        'image_sets' => [ImageSet::class, 'key' => 'image_set_id'],
    ];
    public $hasMany = [
        'prices'                  => ProductPrice::class,
        'property_values'         => [PropertyValue::class, 'key' => 'variant_id', 'otherKey' => 'id'],
        'reviews'                 => [Review::class],
        'category_review_totals'  => [CategoryReviewTotal::class, 'conditions' => 'product_id is null'],
        'cart_products'           => CartProduct::class,
        'order_products'          => OrderProduct::class,
        'product_property_values' => [
            PropertyValue::class,
            'key'      => 'product_id',
            'otherKey' => 'product_id',
            'scope'    => 'productOnly',
        ],
    ];
    public $morphMany = [
        'customer_group_prices' => [CustomerGroupPrice::class, 'name' => 'priceable'],
        'additional_prices'     => [Price::class, 'name' => 'priceable'],
    ];
    protected $fillable = [
        'product_id',
        'user_defined_id',
        'image_set_id',
        'stock',
        'name',
        'published',
        'weight',
        'length',
        'width',
        'height',
        'allow_out_of_stock_purchases',
        'mpn',
        'gtin',
    ];

    public function afterSave()
    {
        if ( ! $this->isBackendRelationUpdate()) {
            return;
        }

        if ($this->image_set_id === null) {
            $this->createImageSetFromTempImages();
        }

        $this->handlePropertyValueUpdates();
    }

    /**
     * Handle the form data form the property value form.
     */
    protected function handlePropertyValueUpdates()
    {
        $locales = Locale::isEnabled()->get();

        $formData = array_wrap(post('VariantPropertyValues', []));
        if (count($formData) < 1) {
            PropertyValue::where('variant_id', $this->id)->delete();
        }

        $properties     = Property::whereIn('id', array_keys($formData))->get();
        $propertyValues = PropertyValue::where('variant_id', $this->id)->get();

        foreach ($formData as $id => $value) {
            $property = $properties->find($id);
            $pv       = $propertyValues->where('property_id', $id)->first()
                ?? new PropertyValue([
                    'variant_id'  => $this->id,
                    'product_id'  => $this->product_id,
                    'property_id' => $id,
                ]);

            $pv->value = $value;
            foreach ($locales as $locale) {
                $transValue = post(
                    sprintf('RLTranslate.%s.VariantPropertyValues.%d', $locale->code, $id),
                    post(sprintf('VariantPropertyValues.%d', $id)) // fallback
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

            if (($pv->value === null || $pv->value === '') && $pv->exists) {
                $pv->delete();
            } else {
                $pv->save();
            }
        }
    }

    public function afterDelete()
    {
        DB::table('offline_mall_property_values')->where('variant_id', $this->id)->delete();
        DB::table('offline_mall_wishlist_items')->where('variant_id', $this->id)->delete();
    }

    protected function createImageSetFromTempImages()
    {
        // Only run this if a variant relation has been created/updated.
        if ( ! $this->isBackendRelationUpdate()) {
            return;
        }

        $tempImages = $this->temp_images()
                           ->withDeferred(post('_session_key'))
                           ->count();

        if ($tempImages < 1) {
            return;
        }

        return DB::transaction(function () {
            $set             = new ImageSet();
            $set->name       = $this->name;
            $set->product_id = $this->product_id;
            $set->save();

            $this->image_set_id = $set->id;
            $this->save();

            $this->commitDeferred(post('_session_key'));

            return DB::table('system_files')
                     ->where('attachment_type', Variant::MORPH_KEY)
                     ->where('attachment_id', $this->id)
                     ->where('field', 'temp_images')
                     ->update([
                         'attachment_type' => ImageSet::MORPH_KEY,
                         'attachment_id'   => $set->id,
                         'field'           => 'images',
                     ]);
        });
    }

    public function getImageSetIdOptions()
    {
        $null = [
            '' => '-- ' . trans('offline.mall::lang.image_sets.create_new'),
        ];

        $sets = Product::find(post('id', $this->product_id))->image_sets;
        if ( ! $sets) {
            return $null;
        }

        return $null + $sets->pluck('name', 'id')->toArray();
    }

    public function custom_fields()
    {
        return $this->product->custom_fields();
    }

    public function scopePublished($query)
    {
        return $query->where('published', true);
    }

    public function getAllPropertyValuesAttribute()
    {
        return $this->product_property_values
            ? $this->product_property_values->concat($this->property_values)
            : $this->property_values;
    }

    public function getAttribute($attribute)
    {
        $originalValue       = parent::getAttribute($attribute);
        $inheritanceDisabled = session()->get('mall.variants.disable-inheritance');

        // If any of the product relation columns are called don't override the method's default behaviour.
        $dontInheritAttribute = \in_array($attribute, ['product', 'product_id', 'all_property_values']);
        if ($dontInheritAttribute || $inheritanceDisabled || ! $this->product_id) {
            return $originalValue;
        }

        $parentValues = $this->product->getAttribute($attribute);

        // In case of an empty Array or Collection we want to
        // return the parent's values.
        $empty = $this->isEmpty($attribute, $originalValue);

        if ($attribute !== 'prices' || $empty) {
            return $originalValue === null || $empty ? $parentValues : $originalValue;
        }

        // Inherit parent pricing info.
        return $originalValue->map(function ($price) use ($parentValues) {
            return $price->price === null ? $this->nullPrice($price->currency, $parentValues) : $price;
        });
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
                'variant_id'  => $this->id,
                'product_id'  => $this->product->id,
                'currency_id' => Currency::where('code', $currency)->firstOrFail()->id,
            ], [
                'price' => $price === null ? null : ($price / 100),
            ]);
        }
    }

    public function getVariantIdAttribute()
    {
        return $this->id;
    }

    public function getVariantHashIdAttribute()
    {
        return $this->hashId;
    }

    public function getProductHashIdAttribute()
    {
        return $this->product->hashId;
    }

    /**
     * Return the hashId with a 'variant-' prefix.
     */
    public function getPrefixedHashIdAttribute()
    {
        return 'variant-' . $this->getHashIdAttribute();
    }

    /**
     * Return the id with a 'variant-' prefix.
     */
    public function getPrefixedIdAttribute()
    {
        return 'variant-' . $this->id;
    }

    protected function isEmpty($attribute, $originalValue): bool
    {
        if ($attribute === 'description' || $attribute === 'description_short') {
            $originalValue = trim(Html::strip($originalValue));

            return $originalValue === '';
        }

        if ($originalValue instanceof Collection) {
            return $originalValue->count() < 1;
        }

        if (is_array($originalValue)) {
            return count($originalValue) < 1;
        }

        return false;
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
			->whereHas('product',function($query) {
					$query->where('offline_mall_products.published', 1);
				})
            ->with('product')
            ->get()
            ->map(function (self $variant) use ($cmsPage, $page, $url) {
                $pageUrl = $cmsPage->url($page, [
                    'slug'    => $variant->product->slug,
                    'variant' => $variant->variantHashId,
                ]);

                return [
                    'title'    => $variant->name,
                    'url'      => $pageUrl,
                    'mtime'    => $variant->updated_at,
                    'isActive' => $pageUrl === $url,
                ];
            })
            ->toArray();

        return [
            'items' => $items,
        ];
    }

    protected function isBackendRelationUpdate(): bool
    {
        return app()->runningInBackend()
            && request('Variant')
            && request('MallPrice')
            && request('_relation_field') === 'variants'
            && starts_with(request()->header('X-OCTOBER-REQUEST-HANDLER'), 'onRelationManage');
    }
}
