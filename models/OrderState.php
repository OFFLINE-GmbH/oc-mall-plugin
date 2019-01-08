<?php namespace OFFLINE\Mall\Models;

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

    public $implement = ['@RainLab.Translate.Behaviors.TranslatableModel'];
    protected $dates = ['deleted_at'];

    public $translatable = [
        'name',
        'description',
    ];
    public $rules = [
        'name' => 'required',
    ];

    public $table = 'offline_mall_order_states';

    public $hasMany = [
        'orders' => Order::class,
    ];

    public function getFlagOptions()
    {
        return [
            self::FLAG_CANCELLED => trans('offline.mall::lang.order_states.flags.cancelled'),
            self::FLAG_COMPLETE  => trans('offline.mall::lang.order_states.flags.complete'),
            self::FLAG_NEW       => trans('offline.mall::lang.order_states.flags.new'),
        ];
    }
}
