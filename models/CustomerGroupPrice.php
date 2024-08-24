<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Models;

use October\Rain\Database\Traits\Validation;

class CustomerGroupPrice extends Price
{
    use Validation;

    /**
     * The table associated with this model.
     * @var string
     */
    public $table = 'offline_mall_customer_group_prices';

    /**
     * The validation rules for the single attributes.
     * @var array
     */
    public $rules = [ ];

    /**
     * The attributes that are mass assignable.
     * @var array<string>
     */
    public $fillable = [
        'customer_group_id',
        'currency_id',
        'priceable_id',
        'priceable_type',
        'price',
    ];

    /**
     * The morphto relationships of this model.
     * @var array
     */
    public $morphTo = [
        'priceable' => [],
    ];

    /**
     * Mark this price as specific to the logged in customer. Usable for front-end checks.
     * @var bool
     */
    public $isCustomerSpecific = true;

    /**
     * Holds the official Price model.
     * @var Price
     */
    public $official;

    /**
     * Skip internal query check, as used by the IsState trait.
     * @internal
     * @var boolean
     */
    protected $skipEnabledCheck = true;
}
