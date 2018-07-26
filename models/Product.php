<?php namespace OFFLINE\Mall\Models;

use Model;
use October\Rain\Database\Traits\Sluggable;
use October\Rain\Database\Traits\SoftDelete;
use October\Rain\Database\Traits\Validation;
use OFFLINE\Mall\Classes\Exceptions\OutOfStockException;
use OFFLINE\Mall\Classes\Traits\CustomFields;
use OFFLINE\Mall\Classes\Traits\HashIds;
use OFFLINE\Mall\Classes\Traits\Images;
use OFFLINE\Mall\Classes\Traits\Price;
use OFFLINE\Mall\Classes\Traits\UserSpecificPrice;
use System\Models\File;

class Product extends Model
{
    use Validation;
    use SoftDelete;
    use Sluggable;
    use UserSpecificPrice;
    use Price;
    use Images;
    use CustomFields;
    use HashIds;

    protected $dates = ['deleted_at'];
    public $jsonable = ['links', 'price', 'old_price'];
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
        'name'   => 'required',
        'slug'   => ['regex:/^[a-z0-9\/\:_\-\*\[\]\+\?\|]*$/i'],
        'price'  => 'required',
        'weight' => 'integer|nullable',
    ];
    public $casts = [
        'price_includes_tax'           => 'boolean',
        'allow_out_of_stock_purchases' => 'boolean',
        'weight'                       => 'integer',
        'id'                           => 'integer',
        'stackable'                    => 'boolean',
        'stock'                        => 'integer',
        'shippable'                    => 'boolean',
    ];

    public $table = 'offline_mall_products';
    public $with = ['image_sets'];
    public $attachMany = [
        'downloads' => File::class,
    ];
    public $belongsTo = [
        'category'          => Category::class,
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
        'property_values'       => [PropertyValue::class, 'name' => 'describable'],
        'customer_group_prices' => [CustomerGroupPrice::class, 'name' => 'priceable'],
    ];
    public $hasMany = [
        'variants'      => Variant::class,
        'cart_products' => CartProduct::class,
        'image_sets'    => ImageSet::class,
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
            'table'      => 'offline_mall_cart_products',
            'key'        => 'product_id',
            'otherKey'   => 'cart_id',
            'deleted'    => true,
            'pivot'      => ['id', 'quantity', 'price'],
            'pivotModel' => CartProduct::class,
        ],
    ];

    public function scopePublished($query)
    {
        return $query->where('published', true);
    }

    public function getVariantOptionsAttribute()
    {
        return $this->custom_fields()->whereIn('type', ['dropdown', 'color', 'image'])->get();
    }

    public function reduceStock(int $quantity): self
    {
        $this->stock -= $quantity;
        if ($this->stock < 0 && $this->allow_out_of_stock_purchases !== true) {
            throw new OutOfStockException($this);
        }

        return tap($this)->save();
    }

    /**
     * We are using a simple dropdown for this attribute since the relation
     * widget has some problems with the emptyOption option.
     * @return array
     */
    public function getGroupByPropertyIdOptions()
    {
        return ['' => trans('offline.mall::lang.common.none')]
            + $this->category->properties->pluck('name', 'id')->toArray();
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
