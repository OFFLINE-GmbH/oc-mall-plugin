<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Models;

use Model;
use October\Rain\Database\Traits\Sluggable;
use October\Rain\Database\Traits\Sortable;
use October\Rain\Database\Traits\Validation;
use OFFLINE\Mall\Classes\Traits\NullPrice;
use RainLab\User\Models\User;

class CustomerGroup extends Model
{
    use NullPrice;
    use Sluggable;
    use Sortable;
    use Validation;

    /**
     * Implement behaviors for this model.
     * @var array
     */
    public $implement = ['@RainLab.Translate.Behaviors.TranslatableModel'];

    /**
     * The table associated with this model.
     * @var string
     */
    public $table = 'offline_mall_customer_groups';

    /**
     * The translatable attributes of this model.
     * @var array
     */
    public $translatable = [
        'name',
    ];

    /**
     * The validation rules for the single attributes.
     * @var array
     */
    public $rules = [
        'name' => 'required',
        'code' => 'required',
    ];

    /**
     * The attributes that are mass assignable.
     * @var array<string>
     */
    public $fillable = [
        'name',
        'code',
    ];

    /**
     * Automatically generate unique URL names for the passed attributes.
     * @var array
     */
    public $slugs = [
        'code' => 'name',
    ];

    /**
     * The hasMany relationships of this model.
     * @var array
     */
    public $hasMany = [
        'users'  => [User::class, 'key' => 'offline_mall_customer_group_id'],
        'prices' => [CustomerGroupPrice::class],
    ];

    /**
     * Return Price relationship
     * @param null|string|Currency $currency
     * @return mixed
     */
    public function price($currency = null)
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

        return $this->prices->where('currency_id', $currency)->first() ?? $this->nullPrice($currency, $this->prices);
    }
}
