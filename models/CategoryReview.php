<?php namespace OFFLINE\Mall\Models;

use Model;
use October\Rain\Database\Traits\Validation;

class CategoryReview extends Model
{
    use Validation;

    public $table = 'offline_mall_category_reviews';
    public $rules = [
        'rating'             => 'required|between:1,5',
        'review_id'          => 'required|exists:offline_mall_reviews,id',
        'review_category_id' => 'nullable|exists:offline_mall_review_categories,id',
    ];
    public $fillable = [
        'review_id',
        'review_category_id',
        'rating',
        'approved_at',
    ];
    public $belongsTo = [
        'review'          => Review::class,
        'review_category' => ReviewCategory::class,
    ];
    public $casts = [
        'rating' => 'integer',
    ];
    public $dates = ['approved_at'];

    public function afterSave()
    {
        if ( ! $this->approved_at) {
            return;
        }
        if ($this->review->product) {
            $this->updateRating($this->review->product);
        }
        if ($this->review->variant) {
            $this->updateRating($this->review->variant);
        }
    }

    /**
     * Update rating re-calculates the current overall rating for a Product or Variant
     * in a given ReviewCategory.
     *
     * @param $target
     */
    public function updateRating($target)
    {
        $baseQuery = self
            ::whereHas('review', function ($q) use ($target) {
                $q->when($target instanceof Product, function ($q) use ($target) {
                    $q->where('product_id', $target->id);
                }, function ($q) use ($target) {
                    $q->where('variant_id', $target->id);
                });
            })
            ->where('review_category_id', $this->review_category_id);

        $count = $baseQuery->count();
        $sum   = $baseQuery->sum('rating');

        $key = $target instanceof Product ? 'product_id' : 'variant_id';

        CategoryReviewTotal::updateOrCreate([
            $key                 => $target->id,
            'review_category_id' => $this->review_category_id,
        ], [
            'rating' => round($sum / $count, 2),
        ]);
    }
}
