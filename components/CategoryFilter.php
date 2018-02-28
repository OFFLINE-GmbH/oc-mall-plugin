<?php namespace OFFLINE\Mall\Components;

use Cms\Classes\ComponentBase;
use Illuminate\Support\Collection;
use OFFLINE\Mall\Classes\CategoryFilter\Filter;
use OFFLINE\Mall\Classes\CategoryFilter\QueryString;
use OFFLINE\Mall\Classes\CategoryFilter\RangeFilter;
use OFFLINE\Mall\Classes\CategoryFilter\SetFilter;
use OFFLINE\Mall\Classes\Traits\HashIds;
use OFFLINE\Mall\Classes\Traits\SetVars;
use OFFLINE\Mall\Models\Category as CategoryModel;
use OFFLINE\Mall\Models\Property;
use Session;
use Validator;

class CategoryFilter extends ComponentBase
{
    use SetVars;
    use HashIds;

    public const FILTER_KEY = 'oc-mall.category.filter';

    /**
     * @var Category
     */
    public $category;
    /**
     * All available property filters.
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
     * @var array
     */
    public $priceRange;

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
            'category'        => [
                'title'   => 'offline.mall::lang.common.category',
                'default' => ':slug',
                'type'    => 'dropdown',
            ],
            'showPriceFilter' => [
                'title'   => 'offline.mall::lang.components.categoryFilter.properties.showPriceFilter.title',
                'default' => '1',
                'type'    => 'checkbox',
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
        $this->addJs('https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/11.0.3/nouislider.min.js');
        $this->addCss('https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/11.0.3/nouislider.min.css');
    }

    public function onRun()
    {
        $this->setData();
    }

    public function onSetFilter()
    {
        $data = post('filter', []);
        if (count($data) < 1) {
            return $this->replaceFilter([]);
        }

        $data = collect($data)->mapWithKeys(function ($values, $id) {
            if ( ! $this->isSpecialProperty($id)) {
                $id = $this->decode($id);
            }

            return [$id => $values];
        });

        $properties = Property::whereIn('id', $data->keys())->get();

        $filter = $data->mapWithKeys(function ($values, $id) use ($properties) {
            $property = $this->isSpecialProperty($id) ? $id : $properties->find($id);
            if (array_key_exists('min', $values) && array_key_exists('max', $values)) {
                return $values['min'] === '' && $values['max'] === ''
                    ? []
                    : [$id => new RangeFilter($property, $values['min'] ?? null, $values['max'] ?? null)];
            }

            // Remove empty set values
            $values = array_filter($values);

            return count($values) ? [$id => new SetFilter($property, $values)] : [];
        });

        $filter = $this->hashKeys($filter);

        return $this->replaceFilter($filter);
    }

    protected function setData()
    {
        $this->setVar('category', $this->getCategory());
        $this->setVar('props', $this->getProps());
        $this->setVar('filter', $this->getFilter());
        $this->setVar('showPriceFilter', (bool)$this->property('showPriceFilter'));
        $this->setPriceRange();
    }

    protected function getCategory()
    {
        $category = $this->property('category');

        $with = [
            'properties',
            'properties.property_values',
            'properties.property_values.property',
        ];

        if ($category === ':slug') {
            return CategoryModel::getByNestedSlug($this->param('slug'), $with);
        }

        return CategoryModel::with($with)->findOrFail($category);
    }

    protected function setPriceRange()
    {
        $products = $this->category->getProducts();
        $this->setVar('priceRange', [$products->min('price'), $products->max('price')]);
    }

    protected function getProps()
    {
        return $this->category->properties->reject(function (Property $property) {
            return $property->pivot->filter_type === null;
        })->sortBy('pivot.sort_order');
    }

    protected function getFilter()
    {
        $filter = request()->get('filter', []);
        if ( ! is_array($filter)) {
            $filter = [];
        }

        return $this->hashKeys((new QueryString())->deserialize($filter, $this->category));
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

    protected function hashKeys(Collection $data): Collection
    {
        return $data->mapWithKeys(function ($item, $key) {
            $key = $this->isSpecialProperty($key) ? $key : $this->encode($key);

            return [$key => $item];
        });
    }

    protected function isSpecialProperty(string $prop): bool
    {
        return \in_array($prop, Filter::$specialProperties, true);
    }
}
