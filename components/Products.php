<?php namespace OFFLINE\Mall\Components;

use ArrayAccess;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use OFFLINE\Mall\Classes\CategoryFilter\QueryString;
use OFFLINE\Mall\Classes\CategoryFilter\SetFilter;
use OFFLINE\Mall\Classes\CategoryFilter\SortOrder\SortOrder;
use OFFLINE\Mall\Classes\Index\Index;
use OFFLINE\Mall\Models\Category as CategoryModel;
use OFFLINE\Mall\Models\GeneralSettings;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\Variant;

/**
 * The Products components displays a list of Products.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Products extends MallComponent
{
    /**
     * The category to select Products from.
     *
     * @var CategoryModel
     */
    public $category;
    /**
     * Include this category's child categories as well.
     * @var bool
     */
    public $includeChildren;
    /**
     * Display Variants, not Products
     * @var bool
     */
    public $showVariants;
    /**
     * All items to display.
     *
     * @var Product[]|Variant[]
     */
    public $items;
    /**
     * How many items to show per page.
     *
     * @var integer
     */
    public $perPage;
    /**
     * The current page number.
     *
     * @var integer
     */
    public $pageNumber;
    /**
     * The total item count of all pages.
     *
     * @var integer
     */
    public $itemCount;
    /**
     * The name of the product detail page.
     *
     * @var string
     */
    public $productPage;
    /**
     * Show more than one page.
     *
     * @var bool
     */
    public $paginate;
    /**
     * Sort order of the items.
     *
     * @var string
     */
    public $sort;
    /**
     * Set the category's name as page title.
     *
     * @var bool
     */
    public $setPageTitle;
    /**
     * This category and all child category ids as array.
     *
     * @var array
     */
    protected $categories;

    /**
     * Component details.
     *
     * @return array
     */
    public function componentDetails()
    {
        return [
            'name'        => 'offline.mall::lang.components.products.details.name',
            'description' => 'offline.mall::lang.components.products.details.description',
        ];
    }

    /**
     * Properties of this component.
     *
     * @return array
     */
    public function defineProperties()
    {
        return [
            'category'        => [
                'title'   => 'offline.mall::lang.common.category',
                'default' => null,
                'type'    => 'dropdown',
            ],
            'setPageTitle'    => [
                'title'       => 'offline.mall::lang.components.products.properties.set_page_title.title',
                'description' => 'offline.mall::lang.components.products.properties.set_page_title.description',
                'default'     => '0',
                'type'        => 'checkbox',
            ],
            'showVariants'    => [
                'title'       => 'offline.mall::lang.components.products.properties.show_variants.title',
                'description' => 'offline.mall::lang.components.products.properties.show_variants.description',
                'default'     => '1',
                'type'        => 'checkbox',
            ],
            'includeChildren' => [
                'title'       => 'offline.mall::lang.components.products.properties.include_children.title',
                'description' => 'offline.mall::lang.components.products.properties.include_children.description',
                'default'     => '0',
                'type'        => 'checkbox',
            ],
            'perPage'         => [
                'title'       => 'offline.mall::lang.components.products.properties.per_page.title',
                'description' => 'offline.mall::lang.components.products.properties.per_page.description',
                'default'     => '9',
                'type'        => 'string',
            ],
            'paginate'        => [
                'title'       => 'offline.mall::lang.components.products.properties.paginate.title',
                'description' => 'offline.mall::lang.components.products.properties.paginate.description',
                'default'     => '1',
                'type'        => 'checkbox',
            ],
            'sort'            => [
                'title'       => 'offline.mall::lang.components.products.properties.sort.title',
                'description' => 'offline.mall::lang.components.products.properties.sort.description',
                'default'     => null,
                'type'        => 'dropdown',
            ],
        ];
    }

    /**
     * Options array for the category dropdown.
     *
     * @return array
     */
    public function getCategoryOptions()
    {
        return [
                null    => trans('offline.mall::lang.components.products.properties.no_category_filter'),
                ':slug' => trans('offline.mall::lang.components.products.properties.use_url'),
            ]
            + CategoryModel::get()->pluck('name', 'id')->toArray();
    }

    /**
     * Options array for the sort order dropdown.
     *
     * @return array
     */
    public function getSortOptions()
    {
        return [null => trans('offline.mall::lang.common.none')] + SortOrder::dropdownOptions();
    }

    /**
     * The component is executed.
     *
     * @return string|void
     */
    public function onRun()
    {
        try {
            $this->setData();
        } catch (ModelNotFoundException $e) {
            return $this->controller->run('404');
        }

        // If a category is selected and the page title should be set, do so.
        if ($this->category && $this->setPageTitle) {
            $this->page->title            = $this->category->meta_title ?: $this->category->name;
            $this->page->meta_description = $this->category->meta_description;
        }
    }

    /**
     * This method sets all variables needed for this component to work.
     *
     * @return void
     */
    protected function setData()
    {
        $this->setVar('includeChildren', (bool)$this->property('includeChildren'));
        $this->setVar('setPageTitle', (bool)$this->property('setPageTitle'));
        $this->setVar('showVariants', (bool)$this->property('showVariants'));
        $this->setVar('paginate', (bool)$this->property('paginate'));
        $this->setVar('sort', $this->property('sort'));
        $this->setVar('category', $this->getCategory());

        if ($this->category) {
            $categories = [$this->category->id];
            if ($this->includeChildren) {
                $categories = $this->category->getChildrenIds();
            }

            $this->setVar('categories', $categories);
        }

        $this->setVar('productPage', GeneralSettings::get('product_page'));
        $this->setVar('pageNumber', (int)request('page', 1));
        $this->setVar('perPage', (int)$this->property('perPage'));
        $this->setVar('items', $this->getItems());
    }

    /**
     * Retrieve all items for the current page from the index.
     *
     * @return ArrayAccess
     */
    protected function getItems(): ArrayAccess
    {
        $filters   = $this->getFilters();
        $sortOrder = $this->getSortOrder();

        $model    = $this->showVariants ? new Variant() : new Product();
        $useIndex = $this->showVariants ? 'variants' : 'products';

        /** @var Index $index */
        $index  = app(Index::class);
        $result = $index->fetch($useIndex, $filters, $sortOrder, $this->perPage, $this->pageNumber);

        // Every id that is not an int is a "ghosted" variant, with an id like
        // product-1. These ids have to be fetched separately. This enables us to
        // query variants and products that don't have any variants from the same index.
        $itemIds  = array_filter($result->ids, 'is_int');
        $ghostIds = array_diff($result->ids, $itemIds);

        $models = $model->with($this->productIncludes())->find($itemIds);
        $ghosts = $this->getGhosts($ghostIds);

        return $this->paginate(
            $models->concat($ghosts),
            $result->totalCount
        );
    }

    /**
     * Fetch all ghost products.
     *
     * Products that don't have any Variants are still stored in the
     * Variants index to make it easier to query everything at once.
     * This method removes the product-X prefix from the ID and fetches
     * the effective Product models to display.
     *
     * @param array $ids
     *
     * @return Collection
     */
    protected function getGhosts(array $ids)
    {
        if (count($ids) < 1) {
            return collect([]);
        }

        $ids = array_map(function ($id) {
            return (int)str_replace('product-', '', $id);
        }, $ids);

        return Product::with($this->productIncludes())->find($ids);
    }

    /**
     * Paginate the result set.
     *
     * @param Collection $items
     * @param int        $totalCount
     *
     * @return LengthAwarePaginator
     */
    protected function paginate(Collection $items, int $totalCount)
    {
        $paginator = new LengthAwarePaginator(
            $items,
            $totalCount,
            $this->perPage,
            $this->pageNumber
        );

        $filter = request()->get('filter', []);
        $paginator->appends('filter', $filter);

        $pageUrl = $this->controller->pageUrl(
            $this->page->fileName,
            ['slug' => $this->param('slug')]
        );

        return $paginator->setPath($pageUrl);
    }

    /**
     * Retrieve the Category by ID or from the page's :slug parameter.
     *
     * @return Collection|null
     */
    protected function getCategory()
    {
        if ($this->property('category') === null) {
            return null;
        }

        if ($this->property('category') === ':slug' && $this->param('slug') === null) {
            throw new \LogicException(
                'OFFLINE.Mall: A :slug URL parameter is needed when selecting products by category slug.'
            );
        }

        return CategoryModel::bySlugOrId($this->param('slug'), $this->property('category'));
    }

    /**
     * Deserialize the URL parameter into Filter classes.
     *
     * @return Collection
     */
    protected function getFilters(): Collection
    {
        if ( ! $this->category) {
            return collect([]);
        }

        $filter = request()->get('filter', []);
        if ( ! is_array($filter)) {
            $filter = [];
        }

        $filters = (new QueryString())->deserialize($filter, $this->category);
        $filters->put('category_id', new SetFilter('category_id', $this->categories));

        return $filters;
    }

    /**
     * Get the sort order selected by the shop admin or the user.
     * Use fallback if neither is present.
     *
     * @return SortOrder
     */
    protected function getSortOrder(): SortOrder
    {
        $key = $this->sort ?? input('sort', SortOrder::default());

        return SortOrder::fromKey($key);
    }

    /**
     * Return an array of default Product includes.
     *
     * @return array
     */
    protected function productIncludes(): array
    {
        return ['image_sets.images'];
    }
}
