<?php namespace OFFLINE\Mall\Models;

use Illuminate\Support\Collection;
use October\Rain\Database\Traits\Nullable;
use October\Rain\Database\Traits\SoftDelete;
use October\Rain\Database\Traits\Validation;
use OFFLINE\Mall\Classes\Exceptions\OutOfStockException;
use OFFLINE\Mall\Classes\Traits\CustomFields;
use OFFLINE\Mall\Classes\Traits\HashIds;
use OFFLINE\Mall\Classes\Traits\Images;
use OFFLINE\Mall\Classes\Traits\PriceAccessors;
use OFFLINE\Mall\Classes\Traits\UserSpecificPrice;
use System\Models\File;
use Model;

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

    const MORPH_KEY = 'mall.variant';

    public $slugs = [];
    public $nullable = ['image_set_id', 'stock'];
    public $table = 'offline_mall_product_variants';
    public $dates = ['deleted_at'];
    public $with = ['product', 'image_sets', 'prices', 'additional_prices'];
    public $casts = [
        'published'                    => 'boolean',
        'allow_out_of_stock_purchases' => 'boolean',
        'id'                           => 'integer',
        'stock'                        => 'integer',
        'weight'                       => 'integer',
    ];
    public $rules = [
        'name'                         => 'required',
        'product_id'                   => 'required|exists:offline_mall_products,id',
        'stock'                        => 'integer|nullable',
        'weight'                       => 'integer|nullable',
        'published'                    => 'boolean',
        'allow_out_of_stock_purchases' => 'boolean',
    ];
    public $attachMany = [
        'temp_images' => File::class,
        'downloads'   => File::class,
    ];
    public $belongsTo = [
        'product'      => Product::class,
        'cart_product' => CartProduct::class,
        'image_sets'   => [ImageSet::class, 'key' => 'image_set_id'],
    ];
    public $hasMany = [
        'prices'          => ProductPrice::class,
        'property_values' => PropertyValue::class,
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
        'allow_out_of_stock_purchases',
    ];

    public static function boot()
    {
        parent::boot();
        static::saved(function (Variant $variant) {
            if ($variant->image_set_id === null) {
                $variant->createImageSetFromTempImages();
            }

            $values = post('VariantPropertyValues');
            if ( ! $values) {
                return;
            }

            foreach ($values as $id => $value) {
                $pv = PropertyValue::firstOrNew([
                    'variant_id'  => $variant->id,
                    'product_id'  => $variant->product_id,
                    'property_id' => $id,
                ]);

                $pv->value = $value;
                $pv->save();
            }
        });
    }

    protected function createImageSetFromTempImages()
    {
        $tempImages = $this->temp_images()
                           ->withDeferred(post('_session_key'))
                           ->count();

        if ($tempImages < 1) {
            return;
        }

        return \DB::transaction(function () {
            $set             = new ImageSet();
            $set->name       = $this->name;
            $set->product_id = $this->product_id;
            $set->save();

            $this->image_set_id = $set->id;
            $this->save();

            $this->commitDeferred(post('_session_key'));

            return \DB::table('system_files')
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

    public function reduceStock(int $quantity): self
    {
        $this->stock -= $quantity;
        if ($this->stock < 0 && $this->allow_out_of_stock_purchases !== true) {
            throw new OutOfStockException($this);
        }

        return tap($this)->save();
    }

    public function custom_fields()
    {
        return $this->product->custom_fields();
    }

    public function scopePublished($query)
    {
        return $query->where('published', true);
    }

    public function getAttribute($attribute)
    {
        // If any of the product relation columns are called don't override the method's default behaviour.
        if (\in_array($attribute, ['product', 'product_id'])) {
            return parent::getAttribute($attribute);
        }

        $originalValue = parent::getAttribute($attribute);

        if (session()->get('mall.variants.disable-inheritance')) {
            return $originalValue;
        }

        $parentValues = $this->product->getAttribute($attribute);

        // In case of an empty Array or Collection we want to
        // return the parent's values
        $emtpy = false;
        if ($originalValue instanceof Collection) {
            $emtpy = $originalValue->count() < 1;
        } elseif (is_array($originalValue)) {
            $emtpy = count($originalValue) < 1;
        }

        // Inherit parent's pricing information.
        if ($attribute === 'prices') {
            if ($emtpy) {
                return $parentValues;
            }

            return $originalValue->map(function ($price) use ($parentValues) {
                return $price->price !== null
                    ? $price
                    : $parentValues->where('currency_id', $price->currency_id)->first();
            });
        }

        return $originalValue === null || $emtpy ? $parentValues : $originalValue;
    }

    /**
     * This setter makes it easier to set price values
     * in different currencies by providing an array of
     * prices. It is mostly used for unit testing.
     *
     * @internal
     *
     * @param $value
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

    /**
     * To easily generate the correct URL to a Product/Variant
     * we blindly call item.variantId. In this case we return
     * the Variant's hashed ID. If the property is called on a
     * Product model null is returned.
     * @return string
     */
    public function getVariantIdAttribute()
    {
        return $this->hashId;
    }

    public function getPropertiesDescriptionAttribute()
    {
        return $this->propertyValuesAsString();
    }

    public function propertyValuesAsString()
    {
        return $this->property_values
            ->reject(function (PropertyValue $value) {
                return $value->value === '' || $value->value === null || $value->property === null;
            })
            ->map(function (PropertyValue $value) {
                // display_value is already escaped in PropertyValue::getDisplayValueAttribute()
                return sprintf('%s: %s', e($value->property->name), $value->display_value);
            })->implode('<br />');
    }

    protected function notNullthy($value): bool
    {
        if ($value instanceof \Illuminate\Support\Collection) {
            return $value->count() > 0;
        }

        return $value !== null;
    }

    public function groupPriceInCurrency($group, $currency)
    {
        if ($group instanceof CustomerGroup) {
            $group = $group->id;
        }
        if ($currency instanceof Currency) {
            $currency = $currency->id;
        }

        $prices = $this->customer_group_prices;

        return optional($prices->where('currency_id', $currency)->where('customer_group_id', $group)->first())
            ->decimal;
    }

    public function additionalPriceInCurrency($category, $currency = null)
    {
        if ($currency === null) {
            $currency = Currency::activeCurrency()->id;
        }
        if ($currency instanceof Currency) {
            $currency = $currency->id;
        }
        if (is_string($currency)) {
            $currency = Currency::whereCode($currency)->firstOrFail()->id;
        }
        if ($category instanceof PriceCategory) {
            $category = $category->id;
        }

        $prices = $this->additional_prices;

        return optional($prices->where('currency_id', $currency)->where('price_category_id', $category)->first());
    }

    public function oldPriceInCurrencyInteger($currency = null)
    {
        return $this->additionalPriceInCurrency(PriceCategory::OLD_PRICE_CATEGORY_ID, $currency)->integer;
    }

    public function oldPriceInCurrency($currency = null)
    {
        return $this->additionalPriceInCurrency(PriceCategory::OLD_PRICE_CATEGORY_ID, $currency);
    }

    public function oldPrice()
    {
        return $this->additional_prices->where('price_category_id', PriceCategory::OLD_PRICE_CATEGORY_ID);
    }

    public function getOldPriceAttribute()
    {
        return $this->mapCurrencyPrices($this->oldPrice());
    }
}
