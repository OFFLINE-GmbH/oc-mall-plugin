<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Models;

use October\Rain\Database\Traits\Nullable;
use October\Rain\Database\Traits\Validation;

class ProductPrice extends Price
{
    use Nullable;
    use Validation;

    /**
     * The table associated with this model.
     * @var string
     */
    public $table = 'offline_mall_product_prices';

    /**
     * The attributes that are mass assignable.
     * @var array<string>
     */
    public $fillable = [
        'price',
        'currency_id',
        'customer_group_id',
        'product_id',
        'variant_id',
    ];

    /**
     * Attributes which should be set to null, when empty.
     * @var array
     */
    public $nullable = ['price', 'variant_id'];

    /**
     * The belongsTo relationships of this model.
     * @var array
     */
    public $belongsTo = [
        'product'  => Product::class,
        'variant'  => Variant::class,
        'currency' => [
            Currency::class,
            'scope' => 'withDisabled',
        ],
    ];

    /**
     * Clear parent morphTo relationships.
     *
     * @var array
     */
    public $morphTo = [ ];
    
    /**
     * The relationships that should be touched on save.
     * @var array
     */
    protected $touches = ['product', 'variant'];

    /**
     * Skip internal query check, as used by the IsState trait.
     * @internal
     * @var boolean
     */
    protected $skipEnabledCheck = true;
}
