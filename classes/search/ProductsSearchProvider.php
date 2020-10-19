<?php

namespace OFFLINE\Mall\Classes\Search;

use Cms\Classes\Controller;
use DB;
use October\Rain\Support\Collection;
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

        $groupByProducts = GeneralSettings::get('group_search_results_by_product', false);

        // Build a results collection, depending on the $groupByProducts setting.
        $results = new Collection();
        foreach ($matchingProducts->concat($matchingVariants) as $match) {
            // If results should not be grouped by product, just add this match to the collection.
            if ( ! $groupByProducts) {
                $results->push($match);
                continue;
            }
            // If matches should be grouped by product, and this match is a variant, check if
            // the related product is already in the results collection. If not, add it.
            if ($match instanceof Variant) {
                if ( ! $results->has($match->product_id)) {
                    $results->put($match->product_id, $match->product);
                }
                continue;
            }
            // The match is a Product, Add it to the result collection if it is not already there.
            if ( ! $results->has($match->id)) {
                $results->put($match->id, $match);
            }
        }

        // Build the OFFLINE.SiteSearch results collection.
        foreach ($results as $match) {
            $url = $controller->pageUrl($productPage, [
                'slug'    => $match->slug,
                'variant' => $match->variant_hash_id,
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
        return $this->isDefaultLocale()
            ? $this->searchProductsFromDefaultLocale()
            : $this->searchProductsFromCurrentLocale();
    }

    protected function searchVariants()
    {
        return $this->isDefaultLocale()
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
        $ids = $this->getModelIdsForQuery(Product::MORPH_KEY);

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
        $variantIds = $this->getModelIdsForQuery(Variant::MORPH_KEY);
        $productIds = $this->getModelIdsForQuery(Product::MORPH_KEY); // @TODO This query runs twice

        // Then return all matching models via Eloquent.
        return Variant::published()
                      ->whereIn('id', $variantIds)
                      ->orWhereHas('product', function ($q) use ($productIds) {
                          $q->where('published', true)
                            ->whereIn('id', $productIds);
                      })
                      ->get();
    }

    /**
     * Returns the model IDs for the `modelClass` that match the search query
     *
     * @param string $modelClass
     *
     * @return \Illuminate\Support\Collection|\October\Rain\Support\Collection
     */
    protected function getModelIdsForQuery($modelClass)
    {
        $results = DB::table('rainlab_translate_attributes')
                     ->where('model_type', $modelClass)
                     ->where('locale', $this->currentLocale())
                     ->where('attribute_data', 'LIKE', "%{$this->query}%")
                     ->get(['model_id']);

        return collect($results)->pluck('model_id');
    }

    /**
     * Check if a translator is available and if the
     * current locale is the default locale.
     *
     * @return bool
     */
    protected function isDefaultLocale(): bool
    {
        $translator = $this->translator();

        if ( ! $translator) {
            return true;
        }

        return $translator->getLocale() === $translator->getDefaultLocale();
    }

    /**
     * Return the current locale
     *
     * @return string|null
     */
    protected function currentLocale(): ?string
    {
        $translator = $this->translator();

        if ( ! $translator) {
            return null;
        }

        return $translator->getLocale();
    }
}
