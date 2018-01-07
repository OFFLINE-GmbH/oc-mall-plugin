<?php namespace OFFLINE\Mall\Components;

use Cms\Classes\ComponentBase;
use OFFLINE\Mall\Classes\Traits\SetVars;
use OFFLINE\Mall\Models\Category as CategoryModel;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\Variant;

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
            'show_variants' => [
                'title'       => 'offline.mall::lang.components.category.properties.show_variants.title',
                'description' => 'offline.mall::lang.components.category.properties.show_variants.description',
                'default'     => '0',
                'type'        => 'checkbox',
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
        $this->setVar('items', $this->getItems());
    }

    private function getItems()
    {
        $showVariants = (bool)$this->property('show_variants');
        if ( ! $showVariants) {
            return $this->category->publishedProducts;
        }

        $this->category->publishedProducts->load('variants');

        return $this->category->publishedProducts->flatMap(function (Product $product) {
            return $product->inventory_management_method === 'variant' ? $product->variants : [$product];
        });
    }

    private function getCategory()
    {
        $category = $this->property('category');

        if ($category === ':slug') {
            return CategoryModel::getByNestedSlug($this->param('slug'));
        }

        return CategoryModel::findOrFail($category);
    }
}
