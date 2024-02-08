<?php declare(strict_types=1);

namespace OFFLINE\Mall\Models;

use RainLab\User\Models\User as UserBase;

class User extends UserBase
{
    /**
     * The validation rules for the single attributes.
     * @var array
     */
    public $rules = [
        'email'                 => 'required|between:6,255|email',
        'avatar'                => 'nullable|image|max:4000',
        'password'              => 'required:create|between:4,255|confirmed',
        'password_confirmation' => 'required_with:password|between:4,255',
    ];

    /**
     * The relations to eager load on every query.
     * @var array
     */
    public $with = [
        'customer_group'
    ];

    /**
     * The belongsTo relationships of this model.
     * @var array
     */
    public $belongsTo  =[
        'customer_group' => [CustomerGroup::class, 'key' => 'offline_mall_customer_group_id'],
    ];

    /**
     * The hasOne relationships of this model.
     * @var array
     */
    public $hasOne = [
        'customer' => Customer::class,
    ];
}
