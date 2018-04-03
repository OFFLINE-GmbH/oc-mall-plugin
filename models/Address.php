<?php namespace OFFLINE\Mall\Models;

use Model;
use October\Rain\Database\Traits\SoftDelete;
use October\Rain\Database\Traits\Validation;
use OFFLINE\Mall\Classes\Traits\HashIds;

class Address extends Model
{
    use Validation;
    use SoftDelete;
    use HashIds;

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
        'country'  => Country::class,
    ];

    public function getNameAttribute()
    {
        return $this->getOriginal('name') ?: $this->customer->name;
    }

    public function getOneLinerAttribute(): string
    {
        $parts = array_filter([
            $this->name,
            $this->lines,
            $this->zip . ' ' . $this->city,
            $this->county_or_province,
            $this->country->name,
        ]);

        return implode(', ', $parts);
    }

    public static function byCustomer(Customer $customer)
    {
        return self::where('customer_id', $customer->id);
    }
}
