<?php namespace OFFLINE\Mall\Components;

use Cms\Classes\ComponentBase;
use Cms\Classes\Page;
use Cms\Classes\Theme;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use OFFLINE\Mall\Classes\CategoryFilter\QueryString;
use OFFLINE\Mall\Classes\Traits\SetVars;
use OFFLINE\Mall\Models\Category as CategoryModel;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\Variant;
use Url;

class Category extends ComponentBase
{
    use SetVars;
    /**
     * @var Category
     */
    public $category;
    /**
     * @var Product|Variant
     */
    public $items;
    /**
     * @var integer
     */
    public $perPage;
    /**
     * @var integer
     */
    public $pageNumber;
    /**
     * @var string
     */
    public $productPage;

    public function componentDetails()
    {
        return [
            'name'        => 'offline.mall::lang.components.category.details.name',
            'description' => 'offline.mall::lang.components.category.details.description',
        ];
    }

    public function defineProperties()
    {
        return [
            'category'      => [
                'title'   => 'offline.mall::lang.common.category',
                'default' => ':slug',
                'type'    => 'dropdown',
            ],
            'product_page'  => [
                'title'       => 'offline.mall::lang.components.category.properties.product_page.title',
                'description' => 'offline.mall::lang.components.category.properties.product_page.description',
                'type'        => 'dropdown',
            ],
            'show_variants' => [
                'title'       => 'offline.mall::lang.components.category.properties.show_variants.title',
                'description' => 'offline.mall::lang.components.category.properties.show_variants.description',
                'default'     => '0',
                'type'        => 'checkbox',
            ],
            'per_page'      => [
                'title'       => 'offline.mall::lang.components.category.properties.per_page.title',
                'description' => 'offline.mall::lang.components.category.properties.per_page.description',
                'default'     => '9',
                'type'        => 'string',
            ],
        ];
    }

    public function getCategoryOptions()
    {
        return [':slug' => trans('offline.mall::lang.components.category.properties.use_url')]
            + CategoryModel::get()->pluck('name', 'id')->toArray();
    }

    public function getProduct_PageOptions()
    {
        return Page::listInTheme(Theme::getActiveThemeCode())->pluck('title', 'fileName')->toArray();
    }

    public function onRun()
    {
        $this->setData();
    }

    protected function setData()
    {
        $this->setVar('category', $this->getCategory());
        $this->setVar('productPage', $this->property('product_page'));
        $this->setVar('pageNumber', (int)request('page', 1));
        $this->setVar('perPage', (int)$this->property('per_page'));
        $this->setVar('items', $this->paginate($this->getItems()));
    }

    protected function getItems(): Collection
    {
        $showVariants = (bool)$this->property('show_variants');

        $this->category->publishedProducts->load('variants');

        $items = $this->category->publishedProducts->flatMap(function (Product $product) {
            return $product->inventory_management_method === 'variant' ? $product->variants : [$product];
        });

        $filtered = $this->applyFilters($items);

        if ($showVariants) {
            return $filtered;
        }

        // If the variants should not be listed separately we select
        // all parent products with properties matching the current filter
        // criteria.
        $productIds = $filtered->unique('product_id')->map->product_id;

        return Product::with('variants')->whereIn('id', $productIds)->get();
    }

    protected function getCategory()
    {
        $category = $this->property('category');

        if ($category === ':slug') {
            return CategoryModel::getByNestedSlug($this->param('slug'));
        }

        return CategoryModel::findOrFail($category);
    }

    protected function paginate(Collection $items)
    {
        $paginator = new LengthAwarePaginator(
            $this->getPaginatorSlice($items),
            $items->count(),
            $this->perPage,
            $this->pageNumber
        );

        $pageUrl = $this->controller->pageUrl($this->page->fileName, ['slug' => $this->param('slug')]);

        return $paginator->setPath($pageUrl);
    }

    protected function getPaginatorSlice($items)
    {
        return $items->slice(($this->pageNumber - 1) * $this->perPage, $this->perPage);
    }

    protected function applyFilters($items)
    {
        $filters = (new QueryString())->deserialize(request()->get('filter', []));
        foreach ($filters as $propertyId => $filter) {
            $items = $filter->apply($items);
        }

        return $items;
    }

}
