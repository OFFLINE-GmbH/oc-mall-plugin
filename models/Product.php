<?php namespace OFFLINE\Mall\Models;

use Model;
use October\Rain\Database\Traits\Sluggable;
use October\Rain\Database\Traits\SoftDelete;
use October\Rain\Database\Traits\Validation;
use October\Rain\Support\Collection;
use OFFLINE\Mall\Classes\Traits\Price;
use System\Models\File;

/**
 * Model
 */
class Product extends Model
{
    use Validation;
    use SoftDelete;
    use Sluggable;
    use Price;

    protected $dates = ['deleted_at'];

    public $jsonable = ['links'];

    public $implement = ['@RainLab.Translate.Behaviors.TranslatableModel'];
    public $translatable = [
        'name',
        ['slug', 'index' => true],
        'description_short',
        'description',
        'meta_title',
        'meta_description',
    ];

    public $slugs = [
        'slug' => 'name',
    ];
    public $rules = [
        'name'  => 'required',
        'slug'  => ['regex:/^[a-z0-9\/\:_\-\*\[\]\+\?\|]*$/i'],
        'price' => 'required|regex:/\d+([\.,]\d+)?/i',
    ];
    public $casts = [
        'price_includes_tax' => 'boolean',
        'weight'             => 'int',
        'stackable'          => 'boolean',
        'shippable'          => 'boolean',
    ];

    public $table = 'offline_mall_products';

    public $attachOne = [
        'main_image' => File::class,
    ];

    public $attachMany = [
        'images'    => File::class,
        'downloads' => File::class,
    ];

    public $belongsTo = [
        'category'          => Category::class,
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
        'property_values' => [PropertyValue::class, 'name' => 'describable'],
    ];

    public $hasMany = [
        'variants'      => Variant::class,
        'cart_products' => CartProduct::class,
    ];

    public $belongsToMany = [
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
            'table'      => 'offline_mall_cart_product',
            'key'        => 'product_id',
            'otherKey'   => 'cart_id',
            'deleted'    => true,
            'pivot'      => ['id', 'quantity', 'price'],
            'pivotModel' => CartProduct::class,
        ],
    ];

    /**
     * Return the main image, if one is uploaded. Otherwise
     * use the first available image.
     *
     * @return File
     */
    public function getImageAttribute()
    {
        if ($this->main_image) {
            return $this->main_image;
        }

        if ($this->images) {
            return $this->images->first();
        }
    }

    /**
     * Return all images except the main image.
     *
     * @return Collection
     */
    public function getAdditionalImagesAttribute()
    {
        // If a main image exists for this product we
        // can just return all additional images.
        if ($this->main_image) {
            return $this->images;
        }

        // If no main image is uploaded we have to exclude the
        // alternatively selected main image form the collection.
        $mainImage = $this->image;

        return $this->images->reject(function ($item) use ($mainImage) {
            return $item->id === $mainImage->id;
        });
    }

    public function scopePublished($query)
    {
        return $query->where('published', true);
    }

    public function getVariantOptionsAttribute()
    {
        return $this->custom_fields()->whereIn('type', ['dropdown', 'color', 'image'])->get();
    }

    /**
     * Returns the product's base price with all CustomFieldValue
     * prices added.
     *
     * @param CustomFieldValue[] $value
     *
     * @return int
     */
    public function priceIncludingCustomFieldValues(array $value = []): int
    {
        $price = $this->getOriginal('price');
        if (count($value) < 1) {
            return $price;
        }

        return collect($value)->reduce(function ($total, CustomFieldValue $value) {
            if ( ! $value->custom_field_option) {
                return $total;
            }

            return $total += $value->custom_field_option->getOriginal('price');
        }, $price);
    }

    /**
     * Enforce min and max quantity values for a product.
     *
     * @return int
     */
    public function normalizeQuantity($quantity): int
    {
        if ($this->quantity_min && $quantity < $this->quantity_min) {
            return $this->quantity_min;
        }
        if ($this->quantity_max && $quantity > $this->quantity_max) {
            return $this->quantity_max;
        }

        return $quantity;
    }

    public function getPriceColumns()
    {
        return ['price', 'old_price'];
    }

}
