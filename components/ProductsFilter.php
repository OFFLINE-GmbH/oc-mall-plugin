<?php namespace OFFLINE\Mall\Components;

use DB;
use Illuminate\Support\Collection;
use OFFLINE\Mall\Classes\CategoryFilter\Filter;
use OFFLINE\Mall\Classes\CategoryFilter\QueryString;
use OFFLINE\Mall\Classes\CategoryFilter\RangeFilter;
use OFFLINE\Mall\Classes\CategoryFilter\SetFilter;
use OFFLINE\Mall\Classes\CategoryFilter\SortOrder\SortOrder;
use OFFLINE\Mall\Classes\Utils\Money;
use OFFLINE\Mall\Models\Brand;
use OFFLINE\Mall\Models\Category as CategoryModel;
use OFFLINE\Mall\Models\Currency;
use OFFLINE\Mall\Models\Property;
use OFFLINE\Mall\Models\PropertyGroup;
use Session;
use Validator;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class ProductsFilter extends MallComponent
{
    /**
     * @var string
     */
    public const FILTER_KEY = 'oc-mall.products.filter';
    /**
     * @var CategoryModel
     */
    public $category;
    /**
     * An array of all subcategory ids.
     * @var array
     */
    public $categories;
    /**
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
     * @var Collection
     */
    public $propertyGroups;
    /**
     * @var Collection
     */
    public $props;
    /**
     * @var Collection
     */
    public $filter;
    /**
     * Query-String representation of the active filter
     * @var string
     */
    public $queryString;
    /**
     * @var boolean
     */
    public $showPriceFilter;
    /**
     * @var boolean
     */
    public $showBrandFilter;
    /**
     * @var Collection<Brand>
     */
    public $brands;
    /**
     * @var boolean
     */
    public $includeChildren;
    /**
     * @var boolean
     */
    public $includeVariants;
    /**
     * @var array
     */
    public $priceRange;
    /**
     * @var Currency
     */
    public $currency;
    /**
     * @var string
     */
    public $sortOrder;
    /**
     * @var array
     */
    public $sortOptions;
    /**
     * @var Money
     */
    protected $money;

    public function componentDetails()
    {
        return [
            'name'        => 'offline.mall::lang.components.productsFilter.details.name',
            'description' => 'offline.mall::lang.components.productsFilter.details.description',
        ];
    }

    public function defineProperties()
    {
        return [
            'category'            => [
                'title'   => 'offline.mall::lang.common.category',
                'default' => ':slug',
                'type'    => 'dropdown',
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
            'includeChildren'     => [
                'title'       => 'offline.mall::lang.components.productsFilter.properties.includeChildren.title',
                'description' => 'offline.mall::lang.components.productsFilter.properties.includeChildren.description',
                'default'     => '1',
                'type'        => 'checkbox',
            ],
            'includeVariants'     => [
                'title'       => 'offline.mall::lang.components.productsFilter.properties.includeVariants.title',
                'description' => 'offline.mall::lang.components.productsFilter.properties.includeVariants.description',
                'default'     => '1',
                'type'        => 'checkbox',
            ],
            'includeSliderAssets' => [
                'title'       => 'offline.mall::lang.components.productsFilter.properties.includeSliderAssets.title',
                'description' => 'offline.mall::lang.components.productsFilter.properties.includeSliderAssets.description',
                'default'     => '1',
                'type'        => 'checkbox',
            ],
        ];
    }

    public function getCategoryOptions()
    {
        return [':slug' => trans('offline.mall::lang.components.category.properties.use_url')]
            + CategoryModel::get()->pluck('name', 'id')->toArray();
    }

    public function init()
    {
        if ((bool)$this->property('includeSliderAssets')) {
            $this->addJs('https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/11.0.3/nouislider.min.js');
            $this->addCss('https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/11.0.3/nouislider.min.css');
        }
        $this->money = app(Money::class);
    }

    public function onRun()
    {
        $this->setData();
    }

    public function onSetFilter()
    {
        $sortOrder = $this->getSortOrder();

        $data = collect(post('filter', []));
        if ($data->count() < 1) {
            return $this->replaceFilter([], $sortOrder);
        }

        $properties = Property::whereIn('slug', $data->keys())->get();

        $filter = $data->mapWithKeys(function ($values, $id) use ($properties) {
            $property = Filter::isSpecialProperty($id) ? $id : $properties->where('slug', $id)->first();
            if (array_key_exists('min', $values) && array_key_exists('max', $values)) {
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
            $values = array_filter($values);

            return count($values) ? [$id => new SetFilter($property, $values)] : [];
        });

        return $this->replaceFilter($filter, $sortOrder);
    }

    protected function setData()
    {
        $this->setVar('currency', Currency::activeCurrency());
        $this->setVar('showPriceFilter', (bool)$this->property('showPriceFilter'));
        $this->setVar('showBrandFilter', (bool)$this->property('showBrandFilter'));
        $this->setVar('includeChildren', (bool)$this->property('includeChildren'));
        $this->setVar('includeVariants', (bool)$this->property('includeVariants'));

        $this->setVar('category', $this->getCategory());

        $categories = [$this->category->id];
        if ($this->includeChildren) {
            $categories = $this->category->getChildrenIds();
        }
        $this->setVar('categories', $categories);

        if ($this->showPriceFilter) {
            $this->setPriceRange();
        }
        if ($this->showBrandFilter) {
            $this->setBrands();
        }

        $this->setVar('propertyGroups', $this->getPropertyGroups());
        $this->setVar('props', $this->setProps());
        $this->setVar('filter', $this->getFilter());
        $this->setVar('sortOrder', $this->getSortOrder());
        $this->setVar('sortOptions', SortOrder::options());
    }

    protected function getCategory()
    {
        return CategoryModel::bySlugOrId($this->param('slug'), $this->property('category'));
    }

    protected function setPriceRange()
    {
        $range = $this->getPriceRangeQuery(Currency::defaultCurrency())->first();

        // If the active currency is not the default currency we might have to
        // extend the range by dynamically calculated prices.
        if ($this->currency->id !== Currency::defaultCurrency()->id) {
            $calculatedMin = $range->min * $this->currency->rate;
            $calculatedMax = $range->max * $this->currency->rate;

            $currencyRange = $this->getPriceRangeQuery($this->currency)->first();

            $range->min = $this->smaller($currencyRange->min, $calculatedMin);
            $range->max = $this->bigger($currencyRange->max, $calculatedMax);
        }


        $min = $this->money->round($range->min, $this->currency->decimals);
        $max = $this->money->round($range->max, $this->currency->decimals);

        $this->setVar('priceRange', $min === $max ? false : [$min, $max]);
    }

    protected function getPriceRangeQuery(Currency $currency)
    {
        return DB
            ::table('offline_mall_product_prices')
            ->selectRaw(DB::raw('min(price) as min, max(price) as max'))
            ->join(
                'offline_mall_products',
                'offline_mall_product_prices.product_id', '=', 'offline_mall_products.id'
            )
            ->whereIn('offline_mall_products.category_id', $this->categories)
            ->where('offline_mall_product_prices.currency_id', $currency->id);
    }

    protected function setBrands()
    {
        $brands = \DB::table('offline_mall_products')
                     ->whereIn('offline_mall_products.category_id', $this->categories)
                     ->select('offline_mall_brands.*')
                     ->distinct()
                     ->join('offline_mall_brands', 'offline_mall_products.brand_id', '=', 'offline_mall_brands.id')
                     ->get()
                     ->toArray();

        $this->setVar('brands', Brand::hydrate($brands));
    }

    protected function getPropertyGroups()
    {
        return $this->category->load('property_groups.filterable_properties')
            ->inherited_property_groups->reject(function (PropertyGroup $group) {
                return $group->filterable_properties->count() < 1;
            })->sortBy('pivot.sort_order');
    }

    /**
     * Pull all the properties from all property groups. These are needed
     * to generate possible filter values.
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

    protected function getFilter()
    {
        $filter = request()->get('filter', []);
        if ( ! is_array($filter)) {
            $filter = [];
        }

        return (new QueryString())->deserialize($filter, $this->category);
    }

    protected function getSortOrder(): string
    {
        return input('sort', SortOrder::default());
    }

    protected function replaceFilter($filter, $sortOrder)
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

    public function getMinValue($values)
    {
        return $values->min('value');
    }

    public function getMaxValue($values)
    {
        return $values->max('value');
    }

    protected function smaller($a, $b)
    {
        return $a > $b ? $b : $a;
    }

    protected function bigger($a, $b)
    {
        return $a > $b ? $a : $b;
    }
}
