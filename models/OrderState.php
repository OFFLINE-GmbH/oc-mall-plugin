<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Models;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Lang;
use Model;
use October\Rain\Database\Traits\SoftDelete;
use October\Rain\Database\Traits\Sortable;
use October\Rain\Database\Traits\Validation;
use OFFLINE\Mall\Classes\Database\IsStates;

class OrderState extends Model
{
    use IsStates;
    use SoftDelete;
    use Sortable;
    use Validation;

    /**
     * Disable `is_default` handler on IsStates trait.
     * @var null|string
     */
    public const IS_DEFAULT = null;

    /**
     * Enable `is_enabled` handler on IsStates trait, by passing the column name.
     * @var null|string
     */
    public const IS_ENABLED = 'is_enabled';

    /**
     * Default NEW order state flag
     * @var string
     */
    public const FLAG_NEW = 'NEW';

    /**
     * Default CANCELLED order state flag
     * @var string
     */
    public const FLAG_CANCELLED = 'CANCELLED';

    /**
     * Default COMPLETE order state flag
     * @var string
     */
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
        '@RainLab.Translate.Behaviors.TranslatableModel',
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
        'is_enabled'    => 'nullable|boolean',
    ];

    /**
     * The attributes that are mass assignable.
     * @var array<string>
     */
    public $fillable = [
        'name',
        'description',
        'flag',
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
     * @return array
     */
    public function getFlagOptions(): array
    {
        return array_map(fn (string $val) => Lang::get($val), static::$availableFlagOptions);
    }

    /**
     * Include all payment methods, even disabled ones.
     * @param Builder $query
     * @return void
     */
    public function scopeAll(Builder $query)
    {
        return $query->withDisabled();
    }

    /**
     * Undocumented function
     * @return void
     */
    public function beforeDelete()
    {
        if (!empty($this->flag)) {
            throw new Exception('You cannot delete a flagged order state.');
        }
    }
}
