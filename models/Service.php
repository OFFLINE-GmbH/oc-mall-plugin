<?php namespace OFFLINE\Mall\Models;

use Model;
use October\Rain\Database\Traits\Sluggable;
use October\Rain\Database\Traits\SoftDelete;
use October\Rain\Database\Traits\Validation;
use OFFLINE\Mall\Classes\Traits\SortableRelation;
use DB;

class Service extends Model
{
    use Validation;
    use Sluggable;
    use SortableRelation;
    use SoftDelete;

    public $table = 'offline_mall_services';
    public $implement = ['@RainLab.Translate.Behaviors.TranslatableModel'];
    public $fillable = [
        'name',
        'description',
    ];
    public $rules = [
        'name' => 'required',
    ];
    public $hasMany = [
        'options' => [
            ServiceOption::class,
            'sort'     => 'sort_order ASC',
            'table'    => 'offline_mall_service_options',
            'key'      => 'service_id',
            'otherKey' => 'id',
        ],
    ];
    public $belongsToMany = [
        'products' => [
            Product::class,
            'table'    => 'offline_mall_product_service',
            'key'      => 'service_id',
            'otherKey' => 'product_id',
            'pivot'    => ['required'],
        ],
        'taxes'    => [
            Tax::class,
            'table'    => 'offline_mall_service_tax',
            'key'      => 'service_id',
            'otherKey' => 'tax_id',
        ],
    ];
    public $translatable = [
        'name',
        'description',
    ];
    public $slugs = [
        'code' => 'name',
    ];

    public function afterDelete()
    {
        $this->options->each->delete();
        DB::table('offline_mall_product_service')->where('service_id', $this->id)->delete();
    }
}
