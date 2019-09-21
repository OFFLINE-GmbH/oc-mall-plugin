<?php namespace OFFLINE\Mall\Models;

use Model;

class CategoryReview extends Model
{
    use \October\Rain\Database\Traits\Validation;
    public $table = 'offline_mall_category_reviews';
    public $rules = [
        'rating'             => 'required|between:1,5',
        'review_id'          => 'required|exists:offline_mall_reviews,id',
        'review_category_id' => 'nullable|exists:offline_mall_review_categories,id',
    ];
    public $belongsTo = [
        'review' => Review::class,
    ];
}
