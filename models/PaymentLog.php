<?php namespace OFFLINE\Mall\Models;

use Model;
use October\Rain\Database\Traits\Validation;

class PaymentLog extends Model
{
    use Validation;

    public $jsonable = ['data', 'order_data', 'payment_method'];
    public $table = 'offline_mall_payments_log';
    public $casts = [
        'failed' => 'boolean',
    ];
    public $rules = [
        'failed'         => 'required|boolean',
        'payment_method' => 'required',
    ];

    public static function boot()
    {
        parent::boot();
        static::creating(function (self $log) {
            $log->reference = str_random(16);
        });
    }
}
