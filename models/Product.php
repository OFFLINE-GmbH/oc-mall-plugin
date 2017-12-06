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

    public $jsonable = ['links', 'properties'];

    public $implement = ['@RainLab.Translate.Behaviors.TranslatableModel'];
    public $translatable = [
        'name',
        ['slug', 'index' => true],
        'description_short',
        'description',
        'properties.value',
        'meta_title',
        'meta_description',
    ];

    public $slugs = [
        'slug' => 'name',
    ];

    public $rules = [
        'name'  => 'required',
        'slug'  => ['required', 'regex:/^[a-z0-9\/\:_\-\*\[\]\+\?\|]*$/i'],
        'price' => 'required|regex:/\d+([\.,]\d+)?/i',
    ];
    public $casts = [
        'price_includes_tax' => 'boolean',
        'weight'             => 'int',
        'length'             => 'int',
        'height'             => 'int',
        'width'              => 'int',
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

    public $hasManyThrough = [
        'custom_field_options' => [
            CustomFieldOption::class,
            'key'        => 'product_id',
            'through'    => Variant::class,
            'throughKey' => 'custom_field_id',
        ],
    ];

    public $hasMany = [
        'custom_fields' => CustomField::class,
        'variants'      => Variant::class,
        'cart_products' => CartProduct::class,
    ];

    public $belongsToMany = [
        'categories'      => [
            Category::class,
            'table'    => 'offline_mall_category_product',
            'key'      => 'product_id',
            'otherKey' => 'category_id',
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
        return $this->custom_fields()->where('type', 'dropdown')->get();
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
            return $total += $value->custom_field_option->getOriginal('price');
        }, $price);
    }
}
