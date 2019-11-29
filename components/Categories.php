<?php namespace Offline\Mall\Components;

use Cms\Classes\Page;
use Cms\Classes\Theme;
use Cms\Classes\ComponentBase;
use Offline\Mall\Models\Category;
use Log;

class Categories extends ComponentBase
{
    public $categoryPage;
    public $displayEmpty;
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
                'title'       => 'offline.mall::lang.components.categories.properties.display_empty.title',
                'description' => 'offline.mall::lang.components.categories.properties.display_empty.description',
                'type'        => 'checkbox',
                'default'     => 0,
            ],
            'categoryPage' => [
                'title'       => 'offline.mall::lang.components.categories.properties.category_page.title',
                'description' => 'offline.mall::lang.components.categories.properties.category_page.description',
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
        $this->categoryPage = $this->page['categoryPage'] = $this->property('categoryPage');
        $this->categories = $this->page['categories'] = $this->loadCategories();
        Log::info('base page '.$this->categoryPage);
    }

    /**
     * @param string $componentName
     * @param string $page
     * @return ComponentBase|null
     */
    protected function getComponent(string $componentName, string $page)
    {
        $component = null;

        $page = Page::load(Theme::getActiveTheme(), $page);

        if (!is_null($page)) {
            $component = $page->getComponent($componentName);
        }

        return $component;
    }

       /**
     * A helper function to get the real URL parameter name. For example, slug for posts
     * can be injected as :post into URL. Real argument is necessary if you want to generate
     * valid URLs for such pages
     *
     * @param ComponentBase|null $component
     * @param string $name
     *
     * @return string|null
     */
    protected function urlProperty(ComponentBase $component = null, string $name = '')
    {
        $property = null;

        if ($component !== null && ($property = $component->property($name))) {
            preg_match('/{{ :([^ ]+) }}/', $property, $matches);

            if (isset($matches[1])) {
                $property = $matches[1];
            }
        } else {
            $property = $name;
        }

        return $property;
    }

    /**
     * Load all categories or, depending on the <displayEmpty> option, only those that have blog posts
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
      
        /*
         * Add a "url" helper attribute for linking to each category
         */
        return $this->linkCategories($categories);
    }

    protected function linkCategories($categories)
    {
     //   return $categories;
        
        $productsComponent = $this->getComponent('Products', $this->categoryPage);

        $categories->each(function ($category) use ($productsComponent) {
            $category->setUrl(
                $this->categoryPage,
                $this->controller,
                [
                    'slug' => $this->urlProperty($productsComponent, 'categoryFilter')
                ]
            );

            if ($category->children) {
                $this->linkCategories($category->children);
            }
        });


        return $categories;
        
    }
}
