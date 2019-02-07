<?php namespace OFFLINE\Mall\Updates;

use Artisan;
use DB;
use October\Rain\Database\Updates\Migration;
use OFFLINE\Mall\Classes\Registration\BootServiceContainer;
use OFFLINE\Mall\Classes\Registration\BootTwig;
use OFFLINE\Mall\Console\ReindexProducts;
use Schema;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\ConsoleOutput;


class MigrateCategoriesToBelongstoManyRelation extends Migration
{
    use BootServiceContainer;
    use BootTwig;

    public $app;

    public function __construct()
    {
        $this->app = app();
    }

    public function up()
    {
        // Since the Plugin itself is not loaded during the migration we need to make
        // sure the required services and commands are registered in the app container.
        $this->registerServices();
        $this->registerTwigEnvironment();

        Schema::create('offline_mall_category_product', function ($table) {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('product_id')->unsigned();
            $table->integer('category_id')->unsigned();
            $table->integer('sort_order')->unsigned();
        });

        // Migrate products to new structure. Migrate the category sort order as well.
        $sortOrders = DB::table('offline_mall_category_product_sort_order')->get()->mapWithKeys(function ($item) {
            return [$item->category_id . '-' . $item->product_id => $item->sort_order];
        });

        $products = \DB::table('offline_mall_products')->get();
        $products->each(function ($product, $index) use ($sortOrders) {
            if ($product->category_id === null) {
                return;
            }

            $orderKey  = $product->category_id . '-' . $product->id;
            $sortOrder = $sortOrders[$orderKey] ?? $index;

            DB::table('offline_mall_category_product')->insert([
                'product_id'  => $product->id,
                'category_id' => $product->category_id,
                'sort_order'  => $sortOrder,
            ]);
        });

        Schema::table('offline_mall_products', function ($table) {
            $table->dropColumn(['category_id']);
        });

        Schema::drop('offline_mall_category_product_sort_order');

        // Rebuild the index with the new category structure if there were
        // any products present before this migration.
        if (app()->environment() !== 'testing' && $products->count() > 0) {
            $command = new ReindexProducts();
            $command->setLaravel($this->app);
            $command->run(new StringInput('--force'), new ConsoleOutput());
        }
    }

    public function down()
    {
        Schema::dropIfExists('offline_mall_category_product');
        Schema::table('offline_mall_products', function ($table) {
            $table->integer('category_id')->nullable();
        });
    }
}
