<?php namespace OFFLINE\Mall\Models;

use Model;
use October\Rain\Database\Traits\Validation;

/**
 * @property Customer $customer
 */
class Review extends Model
{
    use Validation;

    public $table = 'offline_mall_reviews';
    public $rules = [
        'title'       => 'required_with:description',
        'description' => 'required_with:title|max:500',
        'rating'      => 'required|numeric|between:1,5',
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
    public $casts = [
        'rating' => 'integer',
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
        $this->user_hash = static::getUserHash();
    }

    public function afterSave()
    {
        if ($this->product) {
            $this->updateRating($this->product);
        }
        if ($this->variant) {
            $this->updateRating($this->variant);
        }
    }

    /**
     * Update rating re-calculates the current overall rating for a Product or Variant.
     *
     * @param $target
     */
    public function updateRating($target)
    {
        $baseQuery = self
            ::when($target instanceof Product, function ($q) use ($target) {
                $q->where('product_id', $target->id);
            }, function ($q) use ($target) {
                $q->where('variant_id', $target->id);
            });

        $count = $baseQuery->count();
        $sum   = $baseQuery->sum('rating');

        $target->reviews_rating = round($sum / $count, 2);
        $target->save();
    }

    /**
     * The user hash is used to identify anonymous reviewers. This comes
     * in handy when implementing rate limiting.
     * @return string
     */
    public static function getUserHash()
    {
        $data = implode('-', [request()->ip(), request()->userAgent()]);

        return hash('sha256', $data);
    }

    public function afterDelete()
    {
        $this->category_reviews->each->delete();
    }

    public function getCustomerNameAttribute()
    {
        if ( ! $this->customer) {
            return trans('offline.mall::lang.reviews.anonymous');
        }

        return $this->customer->name;
    }
}
