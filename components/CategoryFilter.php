<?php namespace OFFLINE\Mall\Components;

use Cms\Classes\ComponentBase;
use Illuminate\Support\Collection;
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
            'category' => [
                'title'   => 'offline.mall::lang.common.category',
                'default' => ':slug',
                'type'    => 'dropdown',
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
    }

    public function onSetFilter()
    {
        $data = post('filter', []);
        if (count($data) < 1) {
            return $this->replaceFilter([]);
        }

        $data = collect($data)->mapWithKeys(function ($values, $id) {
            $id = $this->decode($id)[0];

            return [$id => $values];
        });

        $properties = Property::whereIn('id', $data->keys())->get();

        $filter = $data->mapWithKeys(function ($values, $id) use ($properties) {
            if (array_key_exists('min', $values) && array_key_exists('max', $values)) {
                if ($values['min'] === '' && $values['max'] === '') {
                    return [];
                }

                return [$id => new RangeFilter($properties->find($id), $values['min'], $values['max'])];
            }

            return [$id => new SetFilter($properties->find($id), $values)];
        });

        return $this->replaceFilter($filter);
    }

    protected function setData()
    {
        $this->setVar('category', $this->getCategory());
        $this->setVar('props', $this->getProps());
        $this->setVar('filter', $this->getFilter());
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

    protected function getProps()
    {
        return $this->category->properties->reject(function (Property $property) {
            return $property->filter_type === null;
        });
    }

    protected function getFilter()
    {
        return (new QueryString())->deserialize(request()->get('filter', []));
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

    protected function getSessionKey(): string
    {
        return sprintf('%s.%s', self::FILTER_KEY, $this->category->id);
    }
}
