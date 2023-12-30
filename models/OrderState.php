<?php namespace OFFLINE\Mall\Models;

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
     * The database table used by this model.
     * @var string
     */
    public $table = 'offline_mall_order_states';

    /**
     * Behaviors implemented by this model.
     * @var array
     */
    public $implement = ['@RainLab.Translate.Behaviors.TranslatableModel'];

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
     * The attributes that should be mutated to dates.
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The applied validation rules.
     * @var array
     */
    public $rules = [
        'name' => 'required',
    ];

    /**
     * Attributes that support translation, if available.
     * @var array
     */
    public $translatable = [
        'name',
        'description',
    ];

    /**
     * Implement hasMany relationships.
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
}
