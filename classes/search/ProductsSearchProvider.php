<?php

namespace OFFLINE\Mall\Classes\Search;

use Cms\Classes\Controller;
use DB;
use Illuminate\Database\Eloquent\Collection;
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
        $translator = $this->translator();

        return ( ! $translator || $translator->getDefaultLocale() === $translator->getLocale())
            ? $this->searchProductsFromDefaultLocale()
            : $this->searchProductsFromCurrentLocale();
    }

    protected function searchVariants()
    {
        $translator = $this->translator();

        return ( ! $translator || $translator->getDefaultLocale() === $translator->getLocale())
            ? $this->searchVariantsFromDefaultLocale()
            : $this->searchVariantsFromCurrentLocale();
    }

    protected function searchProductsFromDefaultLocale()
    {
        return Product::where('inventory_management_method', 'single')
                        ->published()
                        ->where($this->productQuery())
                        ->get();
    }

    protected function searchVariantsFromDefaultLocale()
    {
        $variantQuery = function ($q) {
            $q->where('name', 'like', "%{$this->query}%")
              ->orWhereHas('product', $this->productQuery());
        };

        return Variant::where($variantQuery)
                        ->published()
                        ->get();
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

    /**
     * Returns all matching products with translated contents.
     *
     * @return Collection
     */
    protected function searchProductsFromCurrentLocale()
    {
        // First fetch all model ids with matching contents.
        $ids = $this->getModelIdsForQuery(Product::class);

        // Then return all matching models via Eloquent.
        return Product::where('inventory_management_method', 'single')
                ->published()
                ->whereIn('id', $ids)
                ->get();
    }

    /**
     * Returns all matching variants with translated contents.
     *
     * @return Collection
     */
    protected function searchVariantsFromCurrentLocale()
    {
        // First fetch all model ids with matching contents.
        $variantIds = $this->getModelIdsForQuery(Variant::class);
        $productIds = $this->getModelIdsForQuery(Product::class); // @TODO This query runs twice

        // Then return all matching models via Eloquent.
        return Variant::published()
            ->whereIn('id', $variantIds)
            ->orWhereHas('product', function ($q) use($productIds) {
                $q->where('published', true)
                  ->whereIn('id', $productIds);
            })
            ->get();
    }

    /**
     * Returns the model IDs for the `modelClass` that match the search query
     *
     * @param string $modelClass
     * @return \Illuminate\Support\Collection|\October\Rain\Support\Collection
     */
    protected function getModelIdsForQuery($modelClass)
    {
        $results = DB::table('rainlab_translate_attributes')
            ->where('model_type', $modelClass)
            ->where('attribute_data', 'LIKE', "%{$this->query}%")
            ->get(['model_id']);

        return collect($results)->pluck('model_id');
    }
}
