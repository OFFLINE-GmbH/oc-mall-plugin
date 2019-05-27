<?php namespace OFFLINE\Mall\Controllers;

use Backend\Behaviors\ReorderController;
use Backend\Classes\Controller;
use BackendMenu;
use Cache;
use DB;
use Flash;
use Lang;
use OFFLINE\Mall\Classes\Index\Index;
use OFFLINE\Mall\Classes\Observers\ProductObserver;
use OFFLINE\Mall\Models\Category;
use OFFLINE\Mall\Models\Product;
use Queue;

class CategoriesProducts extends Controller
{
    public $implement = [
        ReorderController::class,
    ];

    public $reorderConfig = 'config_reorder.yaml';

    public $requiredPermissions = [
        'offline.mall.manage_categories',
        'offline.mall.manage_products',
    ];

    /**
     * @var Category
     */
    public $category;
    /**
     * @var ProductObserver
     */
    public $observer;

    public function __construct(Index $index)
    {
        parent::__construct();
        BackendMenu::setContext('OFFLINE.Mall', 'mall-catalogue', 'mall-categories');

        $this->category = Category::findOrFail($this->params[0]);
        $this->observer = new ProductObserver($index);
    }

    public function reorder()
    {
        $this->addJs('/modules/backend/behaviors/reordercontroller/assets/js/october.reorder.js', 'core');

        $this->pageTitle = $this->pageTitle
            ?: Lang::get($this->getConfig('title', 'backend::lang.reorder.default_title'));

        $this->prepareVars();
    }

    public function onReorder()
    {
        $ids = post('record_ids');
        if ( ! $ids) {
            return;
        }

        foreach ($ids as $order => $id) {
            DB::table('offline_mall_category_product')
              ->where('category_id', $this->category->id)
              ->where('product_id', $id)
              ->update([
                  'sort_order' => $order,
              ]);

            // Flush the cached sort order information.
            Cache::forget(Product::sortOrderCacheKey($id));
            $this->observer->updated(Product::find($id));
        }
    }

    /**
     * Prepares common form data
     */
    protected function prepareVars()
    {
        $this->vars['reorderRecords']       = $this->getRecords();
        $this->vars['reorderModel']         = $this->model;
        $this->vars['reorderSortMode']      = $this->sortMode;
        $this->vars['reorderShowTree']      = $this->showTree;
        $this->vars['reorderToolbarWidget'] = $this->toolbarWidget;
        $this->vars['category']             = $this->category;
    }

    protected function getRecords()
    {
        $orders = DB::table('offline_mall_category_product')
                    ->where('category_id', $this->category->id)
                    ->get(['product_id', 'sort_order'])
                    ->pluck('sort_order', 'product_id');

        $categories = optional($this->category->getAllChildrenAndSelf())->pluck('id');

        return Product
            ::whereHas('categories', function ($q) use ($categories) {
                $q->whereIn('category_id', $categories);
            })
            ->get()
            ->map(function ($product) use ($orders) {
                $product->sort_order = (int)$orders->get($product->id, PHP_INT_MAX);

                return $product;
            })
            ->sortBy('sort_order');
    }
}
