<?php namespace OFFLINE\Mall\Models;

use Model;

/**
 * Model
 */
class PaymentLog extends Model
{
    use \October\Rain\Database\Traits\Validation;

    public $jsonable = ['data', 'order_data'];

    public $table = 'offline_mall_payments_log';

    public $casts = [
        'failed' => 'boolean',
    ];

    public $rules = [
        'failed'         => 'required|boolean',
        'payment_method' => 'required',
    ];
}
