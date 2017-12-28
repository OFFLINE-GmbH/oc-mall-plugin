<?php namespace OFFLINE\Mall\Models;

use Model;

/**
 * Model
 */
class FailedPayment extends Model
{
    use \October\Rain\Database\Traits\Validation;

    public $jsonable = ['data', 'order_data'];

    public $table = 'offline_mall_failed_payments';
}
