<?php

namespace OFFLINE\Mall\Classes\Search;

use Cms\Classes\Controller;
use OFFLINE\Mall\Models\GeneralSettings;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\Variant;
use OFFLINE\SiteSearch\Classes\Providers\ResultsProvider;

class ProductsSearchProvider extends ResultsProvider
{
    public function search()
    {
        $matchingProducts = $this->searchProducts();
        $matchingVariants = $this->searchVariants();

        $controller  = new Controller();
        $productPage = GeneralSettings::get('product_page');

        foreach ($matchingProducts->concat($matchingVariants) as $match) {
            $url = $controller->pageUrl($productPage, [
                'slug'    => $match->slug,
                'variant' => $match->variant_id,
            ]);

            $result = $this->newResult();

            $result->relevance = 1;
            $result->title     = $match->name;
            $result->text      = $match->description;
            $result->url       = $url;
            $result->thumb     = $match->image;
            $result->model     = $match;
            $result->meta      = [
                'is_product' => true,
            ];

            $this->addResult($result);
        }

        return $this;
    }

    public function displayName()
    {
        return trans('offline.mall::lang.common.product');
    }

    public function identifier()
    {
        return 'OFFLINE.Mall';
    }

    protected function searchProducts()
    {
        return Product::where('inventory_management_method', 'single')
                      ->where($this->productQuery())
                      ->get();
    }

    protected function searchVariants()
    {
        return Variant::where(function ($q) {
            $q->where('name', 'like', "%{$this->query}%")
              ->orWhereHas('product', $this->productQuery());
        })->get();
    }

    protected function productQuery()
    {
        return function ($q) {
            $q->where('published', true)
              ->where(function ($q) {
                  $q->where('name', 'like', "%{$this->query}%")
                    ->orWhere('meta_title', 'like', "%{$this->query}%")
                    ->orWhere('meta_description', 'like', "%{$this->query}%")
                    ->orWhere('meta_keywords', 'like', "%{$this->query}%")
                    ->orWhere('description', 'like', "%{$this->query}%")
                    ->orWhere('description_short', 'like', "%{$this->query}%")
                    ->orWhere('user_defined_id', 'like', "%{$this->query}%")
                    ->orWhereHas('brand', function ($q) {
                        $q->where('name', 'like', "%{$this->query}%");
                    });
              });
        };
    }
}
