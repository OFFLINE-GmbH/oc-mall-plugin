<?php namespace OFFLINE\Mall\Components;

use Cms\Classes\ComponentBase;
use DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use OFFLINE\Mall\Models\CategoryReview;
use OFFLINE\Mall\Models\Product as ProductModel;
use OFFLINE\Mall\Models\Review;
use OFFLINE\Mall\Models\ReviewCategory;
use RainLab\User\Facades\Auth;

class ProductReviews extends ComponentBase
{
    /**
     * @var ProductModel
     */
    public $product;
    /**
     * @var Collection<Review>
     */
    public $reviews;
    /**
     * @var Collection<Review>
     */
    public $allReviews;
    /**
     * @var Collection<ReviewCategory>
     */
    public $reviewCategories;
    /**
     * @var Review
     */
    public $customerReview;

    public function componentDetails()
    {
        return [
            'name'        => 'offline.mall::lang.components.productReviews.details.name',
            'description' => 'offline.mall::lang.components.productReviews.details.description',
        ];
    }

    public function defineProperties()
    {
        return [
            'product' => [
                'type' => 'string',
            ],
            'variant' => [
                'type' => 'string',
            ],
            'perPage' => [
                'type' => 'string',
            ],
        ];
    }

    public function setData()
    {
        $this->product          = ProductModel::findOrFail($this->property('product'));
        $this->reviewCategories = $this->product->categories->flatMap->inherited_review_categories->unique();

        $this->allReviews       = Review
            ::with(['category_reviews.review_category', 'variant'])
            ->where('product_id', $this->product->id)
            ->orderBy('created_at', 'DESC')
            ->get();

        $this->customerReview = Review
            ::with('category_reviews')
            ->where('product_id', $this->product->id)
            ->when($this->property('variant'), function ($q) {
                $q->where('variant_id', $this->property('variant'));
            })
            ->when(Auth::getUser(), function ($q) {
                $q->where('customer_id', Auth::getUser()->customer->id);
            }, function ($q) {
                $q->where('user_hash', Review::getUserHash());
            })
            ->first();

        $pageNumber    = input('page', 1);
        $perPage       = (int)$this->property('perPage', 5);
        $slice         = $this->allReviews->slice(($pageNumber - 1) * $perPage, $perPage);
        $this->reviews = new LengthAwarePaginator($slice, $this->allReviews->count(), $perPage, $pageNumber);
    }

    public function onRun()
    {
        $this->setData();
    }

    public function onPageChange()
    {
        $this->setData();

        return [
            '.mall-reviews' => $this->renderPartial($this->alias . '::reviews', [
                'reviews' => $this->reviews,
            ]),
        ];
    }

    public function onCreate()
    {
        $this->setData();

        $data         = post();
        $data['pros'] = array_filter(explode("\n", post('pros', '')));
        $data['cons'] = array_filter(explode("\n", post('cons', '')));

        DB::transaction(function () use ($data) {
            // Create the main review.
            $review = new Review();
            $review->fill($data);
            $review->product_id  = $this->property('product');
            $review->variant_id  = $this->property('variant');
            $review->customer_id = optional(optional(Auth::getUser())->customer)->id;
            $review->save();

            // Store any category reviews that are available.
            $categoryRatings = array_filter(post('category_rating', []));
            if (is_array($categoryRatings) && count($categoryRatings) > 0) {
                $this->reviewCategories->each(function (ReviewCategory $category) use ($review, $categoryRatings) {
                    if ($value = array_get($categoryRatings, $category->id)) {
                        CategoryReview::create([
                            'review_id'          => $review->id,
                            'review_category_id' => $category->id,
                            'rating'             => $value,
                        ]);
                    }
                });
            }

            return $review;
        });

        // Refetch latest data.
        $this->setData();

        return $this->refreshFormAndList();
    }

    public function onUpdate()
    {
        $this->setData();

        $data         = post();
        $data['pros'] = array_filter(explode("\n", post('pros', '')));
        $data['cons'] = array_filter(explode("\n", post('cons', '')));

        DB::transaction(function () use ($data) {
            // Update the main review.
            $review = $this->customerReview;
            $review->fill($data);
            $review->save();

            // Update any category reviews that are available.
            $categoryRatings = array_filter(post('category_rating', []));
            if (is_array($categoryRatings) && count($categoryRatings) > 0) {
                $this->reviewCategories->each(function (ReviewCategory $category) use ($review, $categoryRatings) {
                    if ($value = array_get($categoryRatings, $category->id)) {
                        // Fetch the review properly so the update event will be triggered.
                        CategoryReview
                            ::where([
                                'review_id'          => $review->id,
                                'review_category_id' => $category->id,
                            ])
                            ->firstOrFail()
                            ->load(['review.product', 'review.variant'])
                            ->update(['rating' => $value]);
                    }
                });
            }

            return $review;
        });

        // Refetch latest data.
        $this->setData();

        return $this->refreshFormAndList();
    }

    /**
     * @return array
     */
    protected function refreshFormAndList(): array
    {
        return [
            '#mall-rating-widget' => $this->renderPartial($this->alias . '::okay'),
            '.mall-reviews'       => $this->renderPartial($this->alias . '::reviews', [
                'reviews' => $this->reviews,
            ]),
        ];
    }
}
