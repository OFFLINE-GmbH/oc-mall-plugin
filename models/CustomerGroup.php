<?php namespace OFFLINE\Mall\Models;

use Model;
use October\Rain\Database\Traits\Sluggable;
use October\Rain\Database\Traits\Sortable;
use October\Rain\Database\Traits\Validation;
use OFFLINE\Mall\Classes\Traits\NullPrice;

class CustomerGroup extends Model
{
    use Validation;
    use Sortable;
    use Sluggable;
    use NullPrice;

    public $implement = ['@RainLab.Translate.Behaviors.TranslatableModel'];
    public $translatable = ['name'];
    public $table = 'offline_mall_customer_groups';
    public $rules = [
        'name' => 'required',
        'code' => 'required',
    ];
    public $slugs = [
        'code' => 'name',
    ];
    public $hasMany = [
        'users'  => [User::class, 'key' => 'offline_mall_customer_group_id'],
        'prices' => [CustomerGroupPrice::class],
    ];

    public function price($currency)
    {
        if ($currency === null) {
            $currency = Currency::activeCurrency()->id;
        }
        if ($currency instanceof Currency) {
            $currency = $currency->id;
        }
        if (is_string($currency)) {
            $currency = Currency::whereCode($currency)->firstOrFail()->id;
        }

        return $this->prices->where('currency_id', $currency)->first() ?? $this->nullPrice();
    }
}
