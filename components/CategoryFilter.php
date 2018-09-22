<?php namespace OFFLINE\Mall\Components;

use DB;
use Illuminate\Support\Collection;
use OFFLINE\Mall\Classes\CategoryFilter\Filter;
use OFFLINE\Mall\Classes\CategoryFilter\QueryString;
use OFFLINE\Mall\Classes\CategoryFilter\RangeFilter;
use OFFLINE\Mall\Classes\CategoryFilter\SetFilter;
use OFFLINE\Mall\Models\Category as CategoryModel;
use OFFLINE\Mall\Models\Currency;
use OFFLINE\Mall\Models\Property;
use OFFLINE\Mall\Models\PropertyGroup;
use Session;
use Validator;

class CategoryFilter extends MallComponent
{
    /**
     * @var string
     */
    public const FILTER_KEY = 'oc-mall.category.filter';
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

    public function componentDetails()
    {
        return [
            'name'        => 'offline.mall::lang.components.categoryFilter.details.name',
            'description' => 'offline.mall::lang.components.categoryFilter.details.description',
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
                'title'   => 'offline.mall::lang.components.categoryFilter.properties.showPriceFilter.title',
                'default' => '1',
                'type'    => 'checkbox',
            ],
            'includeChildren'     => [
                'title'       => 'offline.mall::lang.components.categoryFilter.properties.includeChildren.title',
                'description' => 'offline.mall::lang.components.categoryFilter.properties.includeChildren.description',
                'default'     => '1',
                'type'        => 'checkbox',
            ],
            'includeVariants'     => [
                'title'       => 'offline.mall::lang.components.categoryFilter.properties.includeVariants.title',
                'description' => 'offline.mall::lang.components.categoryFilter.properties.includeVariants.description',
                'default'     => '1',
                'type'        => 'checkbox',
            ],
            'includeSliderAssets' => [
                'title'       => 'offline.mall::lang.components.categoryFilter.properties.includeSliderAssets.title',
                'description' => 'offline.mall::lang.components.categoryFilter.properties.includeSliderAssets.description',
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
    }

    public function onRun()
    {
        $this->setData();
    }

    public function onSetFilter()
    {
        $data = collect(post('filter', []));
        if ($data->count() < 1) {
            return $this->replaceFilter([]);
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

        return $this->replaceFilter($filter);
    }

    protected function setData()
    {
        $this->setVar('currency', Currency::activeCurrency());
        $this->setVar('showPriceFilter', (bool)$this->property('showPriceFilter'));
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

        $this->setVar('propertyGroups', $this->getPropertyGroups());
        $this->setVar('props', $this->setProps());
        $this->setVar('filter', $this->getFilter());
    }

    protected function getCategory()
    {
        return CategoryModel::bySlugOrId($this->param('slug'), $this->property('category'));
    }

    protected function setPriceRange()
    {
        $range = $this->getPriceRangeQuery()->first();

        $min = round_money($range->min, $this->currency->decimals);
        $max = round_money($range->max, $this->currency->decimals);

        $this->setVar('priceRange', $min === $max ? false : [$min, $max]);
    }

    protected function getPriceRangeQuery()
    {
        return DB
            ::table('offline_mall_product_prices')
            ->selectRaw(DB::raw('min(price) as min, max(price) as max'))
            ->join(
                'offline_mall_products',
                'offline_mall_product_prices.product_id', '=', 'offline_mall_products.id'
            )
            ->whereIn('offline_mall_products.category_id', $this->categories)
            ->where('offline_mall_product_prices.currency_id', $this->currency->id);
    }

    protected function getPropertyGroups()
    {
        return $this->category->load('property_groups.properties')
            ->inherited_property_groups->reject(function (PropertyGroup $group) {
                return $group->properties->count() < 1;
            })->sortBy('pivot.sort_order');
    }

    /**
     * Pull all the properties from all property groups. These are needed
     * to generate possible filter values.
     */
    protected function setProps()
    {
        $this->props = $this->propertyGroups->flatMap->properties->unique();

        $this->values = Property::getValuesForCategory($this->categories);
    }

    protected function getFilter()
    {
        $filter = request()->get('filter', []);
        if ( ! is_array($filter)) {
            $filter = [];
        }

        return (new QueryString())->deserialize($filter, $this->category);
    }

    protected function replaceFilter($filter)
    {
        $this->setData();
        $this->setVar('filter', $filter);

        return [
            'filter'      => $filter,
            'queryString' => (new QueryString())->serialize($filter),
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
}
