<?php namespace Offline\Mall\Components;



use Cms\Classes\Page;
use Cms\Classes\Theme;
use Cms\Classes\ComponentBase;
use Offline\Mall\Models\Category;


class Categories extends ComponentBase
{
    public $categoryPage;
    public $displayEmpty;
    public $displayQuantity;
    public $categories;


    public function componentDetails()
    {
        return [
            'name'        => 'offline.mall::lang.components.categories.details.name',
            'description' => 'offline.mall::lang.components.categories.details.description'
        ];
    }

    public function defineProperties()
    {
        return [
            'displayEmpty' => [
                'title'       => 'offline.mall::lang.components.categories.properties.displayEmpty.title',
                'description' => 'offline.mall::lang.components.categories.properties.displayEmpty.description',
                'type'        => 'checkbox',
                'default'     => 0,
            ],
            'displayQuantity' => [
                'title'       => 'offline.mall::lang.components.categories.properties.displayQuantity.title',
                'description' => 'offline.mall::lang.components.categories.properties.displayQuantity.description',
                'type'        => 'checkbox',
                'default'     => 1,
            ],
            'categoryPage' => [
                'title'       => 'offline.mall::lang.components.categories.properties.categoryPage.title',
                'description' => 'offline.mall::lang.components.categories.properties.categoryPage.description',
                'type'        => 'dropdown',
                'default'     => 'categories',
            ],
        ];
    }

    public function getCategoryPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    
    public function onRun()
    {

        $this->displayEmpty = $this->page['displayEmpty'] = $this->property('displayEmpty');
        $this->displayQuantity = $this->page['displayQuantity'] = $this->property('displayQuantity');
        $this->categoryPage = $this->page['categoryPage'] = $this->property('categoryPage');
        $this->categories = $this->page['categories'] = $this->loadCategories();
    }


    /**
     * Load all categories or, depending on the <displayEmpty> option, only those that have products
     * According to <displayQuantity> it displays products quantities for each categories
     * @return mixed
     */
    protected function loadCategories()
    {
        $categories = Category::with('products_count')->getNested();
     
        if (!$this->property('displayEmpty')) {
            $iterator = function ($categories) use (&$iterator) {

                return $categories->reject(function ($category) use (&$iterator) {
                    if ($category->getNestedProductsCount() == 0) {
                        return true;
                    }
                    if ($category->children) {
                        $category->children = $iterator($category->children);
                    }
                    return false;
                });
            };

        }
      
        return $this->linkCategories($categories);
    }

    protected function linkCategories($categories,$url=null)
    {
        $categories->each(function ($category) use ($url) {
            
           $category->url .= isset($url)?$url."/":""; 
           $category->url .= $category->slug;  
            if ($category->children) {
                $this->linkCategories($category->children,$category->url);
            }
            $category->slug = $category->url;
            $category->url=$this->controller->pageUrl($this->categoryPage, ['slug' => $category->url], false);
        });

        return $categories;
        
    }
}
