<?php namespace OFFLINE\Mall\Models;

use Event;
use Model;
use October\Rain\Database\Traits\Validation;
use RainLab\User\Facades\Auth;

/**
 * @property Customer $customer
 */
class Review extends Model
{
    use Validation;

    public $table = 'offline_mall_reviews';
    public $rules = [
        'title' => 'required_with:description|max:190',
        'description' => 'max:500',
        'rating' => 'required|numeric|between:1,5',
        'product_id' => 'required|exists:offline_mall_products,id',
        'variant_id' => 'nullable|exists:offline_mall_product_variants,id',
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
        'product' => Product::class,
        'variant' => Variant::class,
        'customer' => Customer::class,
    ];
    public $hasMany = [
        'category_reviews' => [CategoryReview::class],
    ];
    public $dates = ['approved_at'];

    public function approve()
    {
        $this->approved_at = now();
        $this->save();

        $this->category_reviews->each->update(['approved_at' => now()]);
    }

    public function afterCreate()
    {
        Event::fire('mall.review.created', [$this]);
    }

    public function afterUpdate()
    {
        Event::fire('mall.review.updated', [$this]);
    }

    public function beforeSave()
    {
        $this->user_hash = static::getUserHash();
    }

    public function afterSave()
    {
        if ( ! $this->approved_at) {
            return;
        }
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
        $sum = $baseQuery->sum('rating');

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
        $user = Auth::getUser();

        $userId = $user->id ?? 0;
        $data = implode('-', [request()->ip(), request()->userAgent(), $userId]);

        return hash('sha256', $data);
    }

    public function afterDelete()
    {
        $this->category_reviews->each->delete();
    }

    public function filterFields($fields)
    {
        if ($this->approved_at !== null && isset($fields->approve_now)) {
            $fields->approve_now->hidden = true;
        }
    }

    public function getCustomerNameAttribute()
    {
        if ( ! $this->customer) {
            return trans('offline.mall::lang.reviews.anonymous');
        }

        return $this->customer->name;
    }

    public function getProsStringAttribute()
    {
        return collect($this->pros)->pluck('value')->filter()->implode("\n");
    }

    public function getConsStringAttribute()
    {
        return collect($this->cons)->pluck('value')->filter()->implode("\n");
    }
}
