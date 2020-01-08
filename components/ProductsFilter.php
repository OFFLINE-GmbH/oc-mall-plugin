<?php namespace OFFLINE\Mall\Components;

use DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use OFFLINE\Mall\Classes\CategoryFilter\Filter;
use OFFLINE\Mall\Classes\CategoryFilter\QueryString;
use OFFLINE\Mall\Classes\CategoryFilter\RangeFilter;
use OFFLINE\Mall\Classes\CategoryFilter\SetFilter;
use OFFLINE\Mall\Classes\CategoryFilter\SortOrder\SortOrder;
use OFFLINE\Mall\Classes\Queries\PriceRangeQuery;
use OFFLINE\Mall\Classes\Utils\Money;
use OFFLINE\Mall\Models\Brand;
use OFFLINE\Mall\Models\Category;
use OFFLINE\Mall\Models\Currency;
use OFFLINE\Mall\Models\Property;
use OFFLINE\Mall\Models\PropertyGroup;
use Session;
use Validator;

/**
 * The ProductsFilter component is used to filter items of
 * a specific category.
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductsFilter extends MallComponent
{
    /**
     * The active category.
     *
     * @var Category
     */
    public $category;
    /**
     * A Collection of all subcategories.
     *
     * @var Collection
     */
    public $categories;
    /**
     * All items in this category.
     *
     * @var Collection<Product|Variant>
     */
    public $items;
    /**
     * All available property values.
     *
     * @var Collection
     */
    public $values;
    /**
     * All available property filters.
     *
     * @var Collection
     */
    public $propertyGroups;
    /**
     * A collection of available Property models.
     *
     * @var Collection
     */
    public $props;
    /**
     * All active Filters.
     *
     * @var Collection
     */
    public $filter;
    /**
     * Query string representation of the active filter.
     *
     * @var string
     */
    public $queryString;
    /**
     * Show the price range filter.
     *
     * @var boolean
     */
    public $showPriceFilter;
    /**
     * Show the brand filter.
     *
     * @var boolean
     */
    public $showBrandFilter;
    /**
     * Show the on sale filter.
     *
     * @var boolean
     */
    public $showOnSaleFilter;
    /**
     * All available brands.
     *
     * @var Collection<Brand>
     */
    public $brands;
    /**
     * Include all items from child categories.
     *
     * @var boolean
     */
    public $includeChildren;
    /**
     * Also filter Variant properties.
     *
     * @var boolean
     */
    public $includeVariants;
    /**
     * The min and max values of the price range.
     *
     * @var array
     */
    public $priceRange;
    /**
     * The active Currency.
     *
     * @var Currency
     */
    public $currency;
    /**
     * The active sort order.
     *
     * @var string
     */
    public $sortOrder;
    /**
     * All available sort Options.
     *
     * @var array
     */
    public $sortOptions;
    /**
     * Sort order of the products component.
     *
     * @var string
     */
    public $productsComponentSort;
    /**
     * Category of the products component.
     *
     * @var Category
     */
    public $productsComponentCategory;
    /**
     * An instance of the money formatter class.
     *
     * @var Money
     */
    protected $money;

    /**
     * Component details.
     *
     * @return array
     */
    public function componentDetails()
    {
        return [
            'name'        => 'offline.mall::lang.components.productsFilter.details.name',
            'description' => 'offline.mall::lang.components.productsFilter.details.description',
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
            'category'            => [
                'title'   => 'offline.mall::lang.common.category',
                'default' => null,
                'type'    => 'dropdown',
            ],
            'includeChildren'     => [
                'title'       => 'offline.mall::lang.components.productsFilter.properties.includeChildren.title',
                'description' => 'offline.mall::lang.components.productsFilter.properties.includeChildren.description',
                'default'     => null,
                'type'        => 'checkbox',
            ],
            'includeVariants'     => [
                'title'       => 'offline.mall::lang.components.productsFilter.properties.includeVariants.title',
                'description' => 'offline.mall::lang.components.productsFilter.properties.includeVariants.description',
                'default'     => null,
                'type'        => 'checkbox',
            ],
            'showPriceFilter'     => [
                'title'   => 'offline.mall::lang.components.productsFilter.properties.showPriceFilter.title',
                'default' => '1',
                'type'    => 'checkbox',
            ],
            'showBrandFilter'     => [
                'title'   => 'offline.mall::lang.components.productsFilter.properties.showBrandFilter.title',
                'default' => '1',
                'type'    => 'checkbox',
            ],
            'showOnSaleFilter'    => [
                'title'   => 'offline.mall::lang.components.productsFilter.properties.showOnSaleFilter.title',
                'default' => '0',
                'type'    => 'checkbox',
            ],
            'includeSliderAssets' => [
                'title'       => 'offline.mall::lang.components.productsFilter.properties.includeSliderAssets.title',
                'description' => 'offline.mall::lang.components.productsFilter.properties.includeSliderAssets.description',
                'default'     => '1',
                'type'        => 'checkbox',
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
        return [':slug' => trans('offline.mall::lang.components.category.properties.use_url')]
            + Category::get()->pluck('name', 'id')->toArray();
    }

    /**
     * The component is initialized.
     *
     * @return void
     */
    public function init()
    {
        if ((bool)$this->property('includeSliderAssets')) {
            $this->addJs('https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/11.0.3/nouislider.min.js');
            $this->addCss('https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/11.0.3/nouislider.min.css');
        }
        $this->money = app(Money::class);
    }

    /**
     * This method sets all variables needed for this component to work.
     *
     * @return void
     */
    protected function setData()
    {
        $this->setVar('currency', Currency::activeCurrency());
        $this->setVar('showPriceFilter', (bool)$this->property('showPriceFilter'));
        $this->setVar('showBrandFilter', (bool)$this->property('showBrandFilter'));
        $this->setVar('showOnSaleFilter', (bool)$this->property('showOnSaleFilter'));

        // The includeChildren and includeVariants properties are set by the
        // products component. If the user specifies explicit values via the
        // component props we can use these instead.
        $includeChildren = $this->property('includeChildren');
        if ($includeChildren !== null) {
            $this->setVar('includeChildren', (bool)$includeChildren);
        }
        $includeVariants = $this->property('includeVariants');
        if ($includeVariants !== null) {
            $this->setVar('includeVariants', (bool)$includeVariants);
        }

        $this->setVar('category', $this->getCategory());

        $categories = collect([$this->category]);
        if ($this->includeChildren) {
            $categories = $this->category->getAllChildrenAndSelf();
        }
        $this->setVar('categories', $categories);

        if ($this->showPriceFilter) {
            $this->setPriceRange();
        }
        if ($this->showBrandFilter) {
            $this->setBrands();
        }

        $this->setVar('propertyGroups', $this->getPropertyGroups());
        $this->setProps();

        $nullOption = [null => trans('offline.mall::frontend.select')];

        $this->setVar('filter', $this->getFilter());
        $this->setVar('sortOrder', $this->getSortOrder());
        $this->setVar('sortOptions', array_merge($nullOption, SortOrder::options(true)));
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
    }

    /**
     * The filter values have been changed.
     *
     * @return array
     */
    public function onSetFilter()
    {
        $sortOrder = $this->getSortOrder();

        $data = collect(post('filter', []));
        if ($data->count() < 1) {
            return $this->replaceFilter(collect([]), $sortOrder);
        }

        $properties = Property::whereIn('slug', $data->keys())->get();
        $filter     = $data->mapWithKeys(function ($values, $id) use ($properties) {
            $property = Filter::isSpecialProperty($id) ? $id : $properties->where('slug', $id)->first();
            if (is_array($values)
                && array_key_exists('min', $values)
                && array_key_exists('max', $values)) {
                if ($values['min'] === '' && $values['max'] === '') {
                    return [];
                }

                return [
                    $id => new RangeFilter(
                        $property, [
                            $values['min'] ?? null,
                            $values['max'] ?? null,
                        ]
                    ),
                ];
            }

            // Remove empty set values
            $values = array_filter(array_wrap($values));

            return count($values) ? [$id => new SetFilter($property, $values)] : [];
        });

        return $this->replaceFilter($filter, $sortOrder);
    }

    /**
     * Set the available price range.
     *
     * This gets the lowest and higest prices from all items
     * of this category.
     *
     * @return void
     */
    protected function setPriceRange()
    {
        $range = (new PriceRangeQuery($this->categories, Currency::defaultCurrency()))->query()->first();

        // If the active currency is not the default currency we might have to
        // extend the range by dynamically calculated prices.
        if ($this->currency->id !== Currency::defaultCurrency()->id) {
            $calculatedMin = $range->min * $this->currency->rate;
            $calculatedMax = $range->max * $this->currency->rate;

            $currencyRange = (new PriceRangeQuery($this->categories, $this->currency))->query()->first();

            $range->min = $this->higher($currencyRange->min, $calculatedMin);
            $range->max = $this->lower($currencyRange->max, $calculatedMax);
        }


        $min = $this->money->round($range->min, $this->currency->decimals);
        $max = $this->money->round($range->max, $this->currency->decimals);

        $this->setVar('priceRange', $min === $max ? false : [$min, $max]);
    }

    /**
     * Fetch all brands that are present in the current category.
     *
     * @return void
     */
    protected function setBrands()
    {
        $brands = \DB::table('offline_mall_products')
                     ->whereIn('offline_mall_category_product.category_id', $this->categories->pluck('id'))
                     ->select('offline_mall_brands.*')
                     ->distinct()
                     ->join('offline_mall_brands', 'offline_mall_products.brand_id', '=', 'offline_mall_brands.id')
                     ->join(
                         'offline_mall_category_product',
                         'offline_mall_products.id', '=', 'offline_mall_category_product.product_id'
                     )
                     ->orderBy('offline_mall_brands.name')
                     ->get()
                     ->toArray();

        $this->setVar('brands', Brand::hydrate($brands));
    }

    /**
     * Get all PropertyGroups in this Category.
     *
     * @return mixed
     */
    protected function getPropertyGroups()
    {
        return $this->category
            ->load('property_groups.translations')
            ->inherited_property_groups
            ->load('filterable_properties.translations')
            ->reject(function (PropertyGroup $group) {
                return $group->filterable_properties->count() < 1;
            })->sortBy('pivot.sort_order');
    }

    /**
     * Pull all the properties from all property groups. These are needed
     * to generate possible filter values.
     *
     * @return void
     */
    protected function setProps()
    {
        $this->values = Property::getValuesForCategory($this->categories);
        $valueKeys    = $this->values->keys();
        $props        = $this->propertyGroups->flatMap->filterable_properties->unique();

        // Remove any property that has no available filters.
        $this->props = $props->filter(function (Property $property) use ($valueKeys) {
            return $valueKeys->contains($property->id);
        });

        $groupKeys = $this->props->pluck('pivot.property_group_id');

        // Remove any property group that has no available properties.
        $this->propertyGroups = $this->propertyGroups->filter(function (PropertyGroup $group) use ($groupKeys) {
            return $groupKeys->contains($group->id);
        });
    }

    /**
     * Get the currently active category.
     *
     * @return mixed
     */
    protected function getCategory()
    {
        // Use the category from the products component if nothing else is specified.
        if ($this->productsComponentCategory && $this->property('category') === null) {
            return $this->productsComponentCategory;
        }

        return Category::bySlugOrId($this->param('slug'), $this->property('category'));
    }

    /**
     * Get the currently active Filter from the QueryString.
     *
     * @return Collection
     */
    protected function getFilter()
    {
        $filter = array_wrap(request()->all() ?? []);

        return (new QueryString())->deserialize($filter, $this->category);
    }

    /**
     * Get the currently active SortOrder.
     *
     * @return string
     */
    protected function getSortOrder(): string
    {
        $fallback = optional($this->productsComponentSort)->key() ?? SortOrder::default();

        return input('sort', $fallback);
    }

    /**
     * Replace the currently active filter query string.
     *
     * @param Collection $filter
     * @param            $sortOrder
     *
     * @return array
     */
    protected function replaceFilter(Collection $filter, $sortOrder)
    {
        $this->setData();
        $this->setVar('filter', $filter);
        $this->setVar('sortOrder', $sortOrder);

        return [
            'filter'      => $filter,
            'sort'        => $sortOrder,
            'queryString' => (new QueryString())->serialize($filter, $sortOrder),
        ];
    }

    /**
     * Get the min value of a Collection.
     *
     * @param $values
     *
     * @return mixed
     */
    public function getMinValue(Collection $values)
    {
        return $values->min('value');
    }

    /**
     * Get the max value of a Collection.
     *
     * @param $values
     *
     * @return mixed
     */
    public function getMaxValue(Collection $values)
    {
        return $values->max('value');
    }

    /**
     * Return the higher of two values.
     *
     * @param $a
     * @param $b
     *
     * @return mixed
     */
    protected function higher($a, $b)
    {
        return $a > $b ? $b : $a;
    }

    /**
     * Return the lower of two values.
     *
     * @param $a
     * @param $b
     *
     * @return mixed
     */
    protected function lower($a, $b)
    {
        return $a > $b ? $a : $b;
    }
}
