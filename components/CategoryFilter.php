<?php namespace OFFLINE\Mall\Components;

use Cms\Classes\ComponentBase;
use OFFLINE\Mall\Classes\Traits\SetVars;
use OFFLINE\Mall\Models\Category as CategoryModel;

class CategoryFilter extends ComponentBase
{
    use SetVars;

    /**
     * @var Category
     */
    public $category;

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

    protected function setData()
    {
        $this->setVar('category', $this->getCategory());
    }

    protected function getCategory()
    {
        $category = $this->property('category');

        if ($category === ':slug') {
            return CategoryModel::getByNestedSlug($this->param('slug'));
        }

        return CategoryModel::findOrFail($category);
    }
}
