<?php namespace OFFLINE\Mall\Components;

use ArrayAccess;
use Flash;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use October\Rain\Exception\ValidationException;
use OFFLINE\Mall\Classes\CategoryFilter\QueryString;
use OFFLINE\Mall\Classes\CategoryFilter\SetFilter;
use OFFLINE\Mall\Classes\CategoryFilter\SortOrder\SortOrder;
use OFFLINE\Mall\Classes\Exceptions\OutOfStockException;
use OFFLINE\Mall\Classes\Index\Index;
use OFFLINE\Mall\Classes\Traits\CustomFields;
use OFFLINE\Mall\Models\Cart as CartModel;
use OFFLINE\Mall\Models\Category as CategoryModel;
use OFFLINE\Mall\Models\Currency;
use OFFLINE\Mall\Models\GeneralSettings;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\Variant;
use RainLab\User\Facades\Auth;
use Redirect;

/**
 * The Products components displays a list of Products.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Products extends MallComponent
{
    use CustomFields;

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
    public $includeVariants;
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
     * ProductsFilter Component.
     *
     * @var ProductsFilter
     */
    public $filterComponent;
    /**
     * Set the category's name as page title.
     *
     * @var bool
     */
    public $setPageTitle;
    /**
     * Google Tag Manager dataLayer code.
     *
     * @var string
     */
    public $dataLayer;
    /**
     * Contains the current category and all child categories.
     *
     * @var Collection
     */
    protected $categories;
    /**
     * Forced filter string
     *
     * @var string
     */
    protected $filter;

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
            'filterComponent' => [
                'title'       => 'offline.mall::lang.components.products.properties.filter_component.title',
                'description' => 'offline.mall::lang.components.products.properties.filter_component.description',
                'default'     => 'productsFilter',
                'type'        => 'string',
            ],
            'filter'          => [
                'title'       => 'offline.mall::lang.components.products.properties.filter.title',
                'description' => 'offline.mall::lang.components.products.properties.filter.description',
                'default'     => null,
                'type'        => 'string',
            ],
            'setPageTitle'    => [
                'title'       => 'offline.mall::lang.components.products.properties.set_page_title.title',
                'description' => 'offline.mall::lang.components.products.properties.set_page_title.description',
                'default'     => '0',
                'type'        => 'checkbox',
            ],
            'includeVariants' => [
                'title'       => 'offline.mall::lang.components.products.properties.include_variants.title',
                'description' => 'offline.mall::lang.components.products.properties.include_variants.description',
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
     * This method sets all variables needed for this component to work.
     *
     * @return void
     */
    protected function setData()
    {
        $this->setVar('includeChildren', (bool)$this->property('includeChildren'));
        $this->setVar('includeVariants', (bool)$this->property('includeVariants'));
        $this->setVar('filter', $this->property('filter'));
        $this->setVar('category', $this->getCategory());

        $filterComponent = $this->controller->findComponentByName($this->property('filterComponent'));
        if ($filterComponent) {
            $filterComponent->productsComponentSort     = $this->getSortOrder();
            $filterComponent->productsComponentCategory = $this->category;
            $filterComponent->includeChildren           = $this->includeChildren;
            $filterComponent->includeVariants           = $this->includeVariants;
            $this->filterComponent                      = $filterComponent;
        }

        $this->setVar('sort', $this->property('sort'));
        $this->setVar('setPageTitle', (bool)$this->property('setPageTitle'));
        $this->setVar('paginate', (bool)$this->property('paginate'));

        if ($this->category) {
            $categories = collect([$this->category]);
            if ($this->includeChildren) {
                $categories = $this->category->getAllChildrenAndSelf();
            }

            $this->setVar('categories', $categories);
        }

        $this->setVar('productPage', GeneralSettings::get('product_page'));
        $this->setVar('pageNumber', (int)request('page', 1));
        $this->setVar('perPage', (int)$this->property('perPage'));
        $this->setVar('items', $this->getItems());

        $this->setVar('dataLayer', $this->handleDataLayer());
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
     * Add a product to the cart.
     *
     * @return mixed
     * @throws ValidationException
     */
    public function onAddToCart()
    {
        $productId = $this->decode(post('product'));
        $variantId = $this->decode(post('variant'));
        $values    = $this->validateCustomFields(post('fields', []));

        $product = Product::published()->findOrFail($productId);
        $variant = null;
        if ($variantId) {
            $variant = Variant::published()->where('product_id', $product->id)->findOrFail($variantId);
        }

        $cart     = CartModel::byUser(Auth::getUser());
        $quantity = (int)post('quantity', $product->quantity_default ?? 1);
        if ($quantity < 1) {
            throw new ValidationException(['quantity' => trans('offline.mall::lang.common.invalid_quantity')]);
        }

        try {
            $cart->addProduct($product, $quantity, $variant, $values);
        } catch (OutOfStockException $e) {
            throw new ValidationException(['stock' => trans('offline.mall::lang.common.stock_limit_reached')]);
        }

        // If the redirect_to_cart option is set to true the user is redirected to the cart.
        if ((bool)GeneralSettings::get('redirect_to_cart', false) === true) {
            $cartPage = GeneralSettings::get('cart_page');

            return Redirect::to($this->controller->pageUrl($cartPage));
        }

        Flash::success(trans('offline.mall::frontend.cart.added'));

        return [
            'added'    => true,
            'item'     => $this->dataLayerArray($product, $variant),
            'currency' => optional(Currency::activeCurrency())->only('symbol', 'code', 'rate', 'decimals'),
            'new_items_count' => optional($cart->products)->count() ?? 0,
            'new_items_quantity' => optional($cart->products)->sum('quantity') ?? 0,
            'quantity' => $quantity,
        ];
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

        $model    = $this->includeVariants ? new Variant() : new Product();
        $useIndex = $this->includeVariants ? 'variants' : 'products';

        $sortOrder->setFilters(clone $filters);

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

        // Preload all pricing information for related products. This is used in case a Variant
        // is inheriting it's parent product's pricing information.
        if ($model instanceof Variant) {
            $models->load(['product.customer_group_prices', 'product.prices', 'product.additional_prices']);
        }

        // Insert the Ghost models back at their old position so the sort order remains.
        $resultSet = collect($result->ids)->map(function ($id) use ($models, $ghosts) {
            return is_int($id)
                ? $models->find($id)
                : $ghosts->find(str_replace('product-', '', $id));
        });

        return $this->paginate(
            $resultSet,
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

        $paginator->appends(request()->all());

        $pageUrl = $this->controller->pageUrl(
            $this->page->fileName,
            ['slug' => $this->param('slug')]
        );

        return $paginator->setPath($pageUrl);
    }

    /**
     * Retrieve the Category by ID or from the page's :slug parameter.
     *
     * @return CategoryModel|null
     */
    protected function getCategory()
    {
        if ($this->category) {
            return $this->category;
        }

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
        $filter = request()->all();
        if ($this->filter) {
            parse_str($this->filter, $filter);
        }

        $filter = array_wrap($filter);

        $filters = (new QueryString())->deserialize($filter, $this->category);
        if ($this->categories) {
            $filters->put('category_id', new SetFilter('category_id', $this->categories->pluck('id')->toArray()));
        }

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
        $key = input('sort', $this->property('sort') ?? SortOrder::default());

        return SortOrder::fromKey($key);
    }

    /**
     * Return an array of default Product includes.
     *
     * @return array
     */
    protected function productIncludes(): array
    {
        return ['translations', 'image_sets.images', 'customer_group_prices', 'additional_prices'];
    }

    /**
     * Generate Google Tag Manager dataLayer code.
     */
    private function handleDataLayer()
    {
        if ( ! $this->page->layout->hasComponent('enhancedEcommerceAnalytics')) {
            return;
        }

        $dataLayer = [
            'ecommerce' => [
                'currencyCode' => Currency::activeCurrency()->code,
                'impressions'  => $this->items->map(function ($item, $index) {
                    $name    = $item instanceof Product ? $item->product : $item->product->name;
                    $variant = $item instanceof Product ? null : $item->name;

                    $category = optional($this->category)->name;
                    $list     = $category ? 'Category ' . $category : '';

                    return [
                        'id'       => $item->prefixedId,
                        'name'     => $name,
                        'price'    => $item->price()->decimal,
                        'brand'    => optional($item->brand)->name,
                        'category' => $category,
                        'variant'  => $variant,
                        'list'     => $list,
                        'position' => $index * $this->pageNumber,
                    ];
                }),
            ],
        ];

        return json_encode($dataLayer);
    }

    /**
     * Return the dataLayer representation of an item.
     *
     * @param null $product
     * @param null $variant
     *
     * @return array
     */
    private function dataLayerArray($product = null, $variant = null)
    {
        $product = $product ?? $this->product;
        $variant = $variant ?? $this->variant;

        $item = $variant ?? $product;

        return [
            'id'       => $item->prefixedId,
            'name'     => $product->name,
            'price'    => $item->price()->decimal,
            'brand'    => optional($item->brand)->name,
            'category' => optional(optional($item->categories)->first())->name,
            'variant'  => optional($variant)->name,
        ];
    }
}
