<?php namespace OFFLINE\Mall\Models;

use Model;
use October\Rain\Database\Traits\Sluggable;
use October\Rain\Database\Traits\Sortable;
use October\Rain\Database\Traits\Validation;

class CustomerGroup extends Model
{
    use Validation;
    use Sortable;
    use Sluggable;
    public $rules = [
        'name' => 'required',
        'code' => 'required',
    ];
    public $slugs = [
        'code' => 'name',
    ];
    public $table = 'offline_mall_customer_groups';

    public $hasMany = [
        'users' => [User::class, 'key' => 'offline_mall_customer_group_id'],
    ];

    public $implement = ['@RainLab.Translate.Behaviors.TranslatableModel'];
    public $translatable = [
        'name',
    ];
}
