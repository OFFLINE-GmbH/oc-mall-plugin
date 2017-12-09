<?php namespace OFFLINE\Mall\Models;

use Model;

/**
 * Model
 */
class Address extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];

    public $rules = [
        'name'            => 'required',
        'lines'           => 'required',
        'zip'             => 'required',
        'county_province' => 'required',
        'country_id'      => 'required|exists:offline_mall_countries,id',
        'city'            => 'required',
    ];

    public $table = 'offline_mall_addresses';

    public $belongsTo = [
        'customer' => Customer::class,
    ];
}
