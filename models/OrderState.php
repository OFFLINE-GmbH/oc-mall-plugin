<?php declare(strict_types=1);

namespace OFFLINE\Mall\Models;

use Lang;
use Model;
use October\Rain\Database\Traits\SoftDelete;
use October\Rain\Database\Traits\Sortable;
use October\Rain\Database\Traits\Validation;

class OrderState extends Model
{
    use Validation;
    use SoftDelete;
    use Sortable;

    public const FLAG_NEW = 'NEW';
    public const FLAG_CANCELLED = 'CANCELLED';
    public const FLAG_COMPLETE = 'COMPLETE';

    /**
     * The available order state flag options.
     * @var array
     */
    public static $availableFlagOptions = [
        self::FLAG_CANCELLED => 'offline.mall::lang.order_states.flags.cancelled',
        self::FLAG_COMPLETE  => 'offline.mall::lang.order_states.flags.complete',
        self::FLAG_NEW       => 'offline.mall::lang.order_states.flags.new',
    ];

    /**
     * Implement behaviors for this model.
     * @var array
     */
    public $implement = [
        '@RainLab.Translate.Behaviors.TranslatableModel'
    ];

    /**
     * The table associated with this model.
     * @var string
     */
    public $table = 'offline_mall_order_states';

    /**
     * The translatable attributes of this model.
     * @var array
     */
    public $translatable = [
        'name',
        'description',
    ];

    /**
     * The validation rules for the single attributes.
     * @var array
     */
    public $rules = [
        'name'          => 'required',
        'is_enabled'    => 'nullable|boolean'
    ];

    /**
     * The attributes that are mass assignable.
     * @var array<string>
     */
    public $fillable = [
        'name',
        'is_enabled',
    ];

    /**
     * The attributes that should be cast.
     * @var array
     */
    public $casts = [
        'is_enabled'    => 'boolean',
        'deleted_at'    => 'datetime',
    ];

    /**
     * The hasMany relationships of this model.
     * @var array
     */
    public $hasMany = [
        'orders' => Order::class,
    ];

    /**
     * Return the available translated orderState flag options.
     *
     * @return array
     */
    public function getFlagOptions(): array
    {
        return array_map(function (string $val) {
            return Lang::get($val);
        }, static::$availableFlagOptions);
    }

    /**
     * Custom scope to retrieve only enabled taxes.
     * @return mixed
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', 1);
    }
}
