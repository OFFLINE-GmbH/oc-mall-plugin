<?php namespace OFFLINE\Mall\Models;

class CustomerGroupPrice extends Price
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * Mark this price as specific to the logged in customer.
     * This property is useful for frontend checks.
     *
     * @var bool
     */
    public $isCustomerSpecific = true;
    /**
     * Holds the official Price model.
     * @var Price
     */
    public $official;

    public $rules = [
    ];
    public $table = 'offline_mall_customer_group_prices';
    public $morphTo = [
        'priceable' => [],
    ];
    public $fillable = [
        'customer_group_id',
        'currency_id',
        'priceable_id',
        'priceable_type',
        'price',
    ];
}
