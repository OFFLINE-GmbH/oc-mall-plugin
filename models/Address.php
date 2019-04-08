<?php namespace OFFLINE\Mall\Models;

use Model;
use October\Rain\Database\Traits\SoftDelete;
use October\Rain\Database\Traits\Validation;
use OFFLINE\Mall\Classes\Traits\HashIds;
use RainLab\Location\Behaviors\LocationModel;

class Address extends Model
{
    use Validation;
    use SoftDelete;
    use HashIds;

    public $implement = [LocationModel::class];

    protected $dates = ['deleted_at'];

    public $rules = [
        'lines'       => 'required',
        'zip'         => 'required',
        'country_id'  => 'required|exists:rainlab_location_countries,id',
        'customer_id' => 'required|exists:offline_mall_customers,id',
        'city'        => 'required',
    ];

    public $fillable = [
        'company',
        'name',
        'lines',
        'zip',
        'country_id',
        'city',
        'state_id',
        'details',
    ];

    public $table = 'offline_mall_addresses';

    public $belongsTo = [
        'customer' => Customer::class,
    ];

    public function getNameAttribute()
    {
        return $this->getOriginal('name') ?: optional($this->customer)->name;
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

    public function getLinesArrayAttribute(): array
    {
        return explode("\n", $this->lines);
    }

    public function getNamesArrayAttribute(): array
    {
        $names = explode("\n", $this->name);
        if (count($names)) {
            return $names;
        }

        return [$this->customer->firstname, $this->customer->lastname];
    }

    public static function byCustomer(Customer $customer)
    {
        return self::where('customer_id', $customer->id);
    }

    public function getCustomerOptions()
    {
        return Customer::with('user')->get()->mapWithKeys(function (Customer $customer) {
            return [
                $customer->id => sprintf('%s (%s)', $customer->name, optional($customer->user)->email),
            ];
        })->toArray();
    }

    public function toArray()
    {
        return [
            'id'          => $this->id,
            'company'     => $this->company,
            'name'        => $this->name,
            'lines'       => $this->lines,
            'zip'         => $this->zip,
            'city'        => $this->city,
            'state_id'    => $this->state_id,
            'state'       => $this->state,
            'country_id'  => $this->country_id,
            'country'     => $this->country,
            'details'     => $this->details,
            'customer_id' => $this->customer_id,
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
            'deleted_at'  => $this->deleted_at,
        ];
    }
}
