<?php namespace OFFLINE\Mall\Models;

use Model;
use RainLab\User\Models\User;

/**
 * Model
 */
class Customer extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];

    protected $casts = [
        'is_guest' => 'boolean',
    ];

    public $rules = [
        'name'     => 'required',
        'is_guest' => 'boolean',
        'user_id'  => 'required|exists:users,id',
    ];

    public $table = 'offline_mall_customers';

    public $belongsTo = [
        'user' => User::class,
    ];
    public $hasMany = [
        'addresses' => Address::class,
    ];
}
