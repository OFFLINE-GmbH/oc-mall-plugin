<?php namespace OFFLINE\Mall\Models;

use Model;

/**
 * Model
 */
class Review extends Model
{
    use \October\Rain\Database\Traits\Validation;

    public $table = 'offline_mall_reviews';
    public $rules = [
        'rating'      => 'required|between:1,5',
        'product_id'  => 'required|exists:offline_mall_products,id',
        'variant_id'  => 'nullable|exists:offline_mall_product_variants,id',
        'customer_id' => 'nullable|exists:offline_mall_customers,id',
    ];
    public $fillable = [
        'rating',
        'title',
        'description',
        'pros',
        'cons',
    ];
    public $jsonable = [
        'pros',
        'cons',
    ];
    public $belongsTo = [
        'product'  => Product::class,
        'variant'  => Variant::class,
        'customer' => Customer::class,
    ];
    public $hasMany = [
        'category_reviews' => [CategoryReview::class],
    ];

    public function beforeSave()
    {
        // The user hash is used to identify anonymous reviewers. This comes
        // in handy when implementing rate limiting.
        $data     = implode('-', [request()->ip(), request()->userAgent()]);
        $userHash = hash('sha256', $data);

        $this->user_hash = $userHash;
    }

    public function afterDelete()
    {
        $this->category_reviews->each->delete();
    }
}
