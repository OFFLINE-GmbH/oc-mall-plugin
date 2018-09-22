<?php namespace OFFLINE\Mall\Components;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use OFFLINE\Mall\Classes\CategoryFilter\QueryString;
use OFFLINE\Mall\Classes\CategoryFilter\RangeFilter;
use OFFLINE\Mall\Classes\CategoryFilter\SetFilter;
use OFFLINE\Mall\Classes\Index\Index;
use OFFLINE\Mall\Models\Category as CategoryModel;
use OFFLINE\Mall\Models\GeneralSettings;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\Variant;
use Url;

class Category extends MallComponent
{
    /**
     * @var CategoryModel
     */
    public $category;
    /**
     * This category and all child category ids.
     *
     * @var array
     */
    public $categories;
    /**
     * @var bool
     */
    public $includeChildren;
    /**
     * @var bool
     */
    public $showVariants;
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
     * @var integer
     */
    public $itemCount;
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
            'category'         => [
                'title'   => 'offline.mall::lang.common.category',
                'default' => ':slug',
                'type'    => 'dropdown',
            ],
            'product_page'     => [
                'title'       => 'offline.mall::lang.components.category.properties.product_page.title',
                'description' => 'offline.mall::lang.components.category.properties.product_page.description',
                'type'        => 'dropdown',
            ],
            'show_variants'    => [
                'title'       => 'offline.mall::lang.components.category.properties.show_variants.title',
                'description' => 'offline.mall::lang.components.category.properties.show_variants.description',
                'default'     => '1',
                'type'        => 'checkbox',
            ],
            'include_children' => [
                'title'       => 'offline.mall::lang.components.category.properties.include_children.title',
                'description' => 'offline.mall::lang.components.category.properties.include_children.description',
                'default'     => '1',
                'type'        => 'checkbox',
            ],
            'per_page'         => [
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

    public function onRun()
    {
        $this->setData();

        $this->page->title            = $this->category->meta_title ?: $this->category->name;
        $this->page->meta_description = $this->category->meta_description;
    }

    protected function setData()
    {
        $this->setVar('includeChildren', (bool)$this->property('include_children'));
        $this->setVar('showVariants', (bool)$this->property('show_variants'));
        $this->setVar('category', $this->getCategory());

        $categories = [$this->category->id];
        if ($this->includeChildren) {
            $categories = $this->category->getChildrenIds();
        }

        $this->setVar('categories', $categories);
        $this->setVar('productPage', GeneralSettings::get('product_page'));
        $this->setVar('pageNumber', (int)request('page', 1));
        $this->setVar('perPage', (int)$this->property('per_page'));
        $this->setVar('items', $this->getItems());
    }

    protected function getItems(): LengthAwarePaginator
    {
        $filters = $this->getFilters();

        $model    = $this->showVariants ? new Variant() : new Product();
        $useIndex = $this->showVariants ? 'variants' : 'products';

        /** @var Index $index */
        $index  = app(Index::class);
        $result = $index->fetch($useIndex, $filters, $this->perPage, $this->pageNumber);

        return $this->paginate(
            $model->with(['image_sets.images'])->find($result->ids),
            $result->totalCount
        );
    }

    protected function getCategory()
    {
        return CategoryModel::bySlugOrId($this->param('slug'), $this->property('category'));
    }

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

        $pageUrl = $this->controller->pageUrl($this->page->fileName, ['slug' => $this->param('slug'),]);

        return $paginator->setPath($pageUrl);
    }

    protected function getFilters()
    {
        $filter = request()->get('filter', []);
        if ( ! is_array($filter)) {
            $filter = [];
        }

        $filters = (new QueryString())->deserialize($filter, $this->category);
        $filters->put('category_id', new SetFilter('category_id', $this->categories));

        return $filters;
    }
}
