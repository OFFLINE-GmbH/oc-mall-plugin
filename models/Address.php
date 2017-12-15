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
        'lines'      => 'required',
        'zip'        => 'required',
        'country_id' => 'required|exists:offline_mall_countries,id',
        'city'       => 'required',
    ];

    public $fillable = [
        'company',
        'name',
        'lines',
        'zip',
        'country_id',
        'city',
        'county_province',
        'details',
    ];

    public $table = 'offline_mall_addresses';

    public $belongsTo = [
        'customer' => Customer::class,
    ];

    public function getNameAttribute()
    {
        return $this->name ?? $this->customer->name;
    }
}
