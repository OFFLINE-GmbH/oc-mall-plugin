<?php namespace OFFLINE\Mall\Console;

use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use OFFLINE\Mall\Classes\Demo\Products\Cruiser1000;
use OFFLINE\Mall\Classes\Demo\Products\Cruiser1500;
use OFFLINE\Mall\Classes\Demo\Products\Cruiser3000;
use OFFLINE\Mall\Classes\Demo\Products\Cruiser3500;
use OFFLINE\Mall\Classes\Demo\Products\Cruiser5000;
use OFFLINE\Mall\Classes\Demo\Products\GiftCard100;
use OFFLINE\Mall\Classes\Demo\Products\GiftCard200;
use OFFLINE\Mall\Classes\Demo\Products\GiftCard50;
use OFFLINE\Mall\Classes\Demo\Products\Jersey;
use OFFLINE\Mall\Classes\Demo\Products\RedShirt;
use OFFLINE\Mall\Classes\Index\Index;
use OFFLINE\Mall\Classes\Index\Noop;
use OFFLINE\Mall\Classes\Index\ProductEntry;
use OFFLINE\Mall\Classes\Index\VariantEntry;
use OFFLINE\Mall\Models\Brand;
use OFFLINE\Mall\Models\Category;
use OFFLINE\Mall\Models\Currency;
use OFFLINE\Mall\Models\Price;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\Property;
use OFFLINE\Mall\Models\PropertyGroup;
use OFFLINE\Mall\Models\ReviewCategory;
use OFFLINE\Mall\Models\Service;
use OFFLINE\Mall\Models\ServiceOption;
use OFFLINE\Mall\Models\Tax;
use Symfony\Component\Console\Input\InputOption;

class SeedDemoData extends Command
{
    protected $name = 'mall:seed-demo';
    protected $description = 'Import OFFLINE.Mall demo data';

    public $bikePropertyGroups = [];
    public $clothingPropertyGroups = [];

    public function handle()
    {
        $question = 'All existing OFFLINE.Mall data will be erased. Do you want to continue?';
        if ( ! $this->option('force') && ! $this->output->confirm($question, false)) {
            return 0;
        }

        // Use a Noop-Indexer so no unnecessary queries are run during seeding.
        // the index will be re-built once everything is done.
        $originalIndex = app(Index::class);
        app()->bind(Index::class, function () {
            return new Noop();
        });

        $this->cleanup();
        $this->createCurrencies();
        $this->createBrands();
        $this->createProperties();
        $this->createReviewCategories();
        $this->createCategories();
        $this->createTaxes();
        $this->createProducts();
        $this->createServices();


        app()->bind(Index::class, function () use ($originalIndex) {
            return $originalIndex;
        });

        Artisan::call('mall:reindex', ['--force' => true]);

        $this->output->success('All done!');
    }

    /**
     * Get the console command arguments.
     * @return array
     */
    protected function getArguments()
    {
        return [];
    }

    /**
     * Get the console command options.
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Don\'t ask before deleting the data.', null],
        ];
    }

    protected function cleanup()
    {
        $this->output->writeln('Resetting plugin data...');
        Artisan::call('plugin:refresh', ['name' => 'OFFLINE.Mall']);
        Artisan::call('cache:clear');

        DB::table('system_files')
          ->where('attachment_type', 'LIKE', 'OFFLINE%Mall%')
          ->orWhere('attachment_type', 'LIKE', 'mall.%')
          ->delete();

        $index = app(Index::class);
        $index->drop(ProductEntry::INDEX);
        $index->drop(VariantEntry::INDEX);
    }

    protected function createProducts()
    {
        $this->output->newLine();
        $this->output->writeln('Creating products...');
        $this->output->newLine();

        $this->output->progressStart(10);

        // Bikes
        (new Cruiser1000())->create();
        $this->output->progressAdvance();

        (new Cruiser1500())->create();
        $this->output->progressAdvance();

        (new Cruiser3000())->create();
        $this->output->progressAdvance();

        (new Cruiser3500())->create();
        $this->output->progressAdvance();

        (new Cruiser5000())->create();
        $this->output->progressAdvance();

        // Clothing
        (new RedShirt())->create();
        $this->output->progressAdvance();

        (new Jersey())->create();

        // Gift Cards
        (new GiftCard50())->create();
        (new GiftCard100())->create();
        (new GiftCard200())->create();

        $this->output->progressFinish();
    }

    protected function createCategories()
    {
        $this->output->writeln('Creating categories...');
        DB::table('offline_mall_categories')->truncate();
        DB::table('offline_mall_category_property_group')->truncate();

        $bikes = Category::create([
            'name'             => 'Bikes',
            'slug'             => 'bikes',
            'code'             => 'bikes',
            'sort_order'       => 0,
            'meta_title'       => 'Bikes, Mountainbikes, Citybikes',
            'meta_description' => 'Take a look at our bikes and find what you are looking for.',
        ]);
        foreach ($this->bikePropertyGroups as $index => $group) {
            $bikes->property_groups()->attach($group, ['relation_sort_order' => $index]);
        }
        ReviewCategory::get()->each(function ($c) use ($bikes) {
            $bikes->review_categories()->attach($c);
        });

        Category::create([
            'name'                      => 'Mountainbikes',
            'slug'                      => 'mountainbikes',
            'code'                      => 'mountainbikes',
            'meta_title'                => 'Mountainbikes',
            'sort_order'                => 0,
            'meta_description'          => 'Take a look at our huge mountainbike range',
            'inherit_property_groups'   => true,
            'inherit_review_categories' => true,
            'parent_id'                 => $bikes->id,
        ]);
        Category::create([
            'name'                      => 'Citybikes',
            'slug'                      => 'citybikes',
            'code'                      => 'citybikes',
            'meta_title'                => 'Citybikes',
            'sort_order'                => 1,
            'meta_description'          => 'Take a look at our huge citybike range',
            'inherit_property_groups'   => true,
            'inherit_review_categories' => true,
            'parent_id'                 => $bikes->id,
        ]);

        $clothing = Category::create([
            'name'             => 'Clothing',
            'slug'             => 'clothing',
            'code'             => 'clothing',
            'sort_order'       => 1,
            'meta_title'       => 'Sports clothes',
            'meta_description' => 'Check out our huge sports clothes range',
        ]);
        foreach ($this->clothingPropertyGroups as $index => $group) {
            $clothing->property_groups()->attach($group, ['relation_sort_order' => $index]);
        }

        Category::create([
            'name'                      => 'Gift cards',
            'slug'                      => 'gift-cards',
            'code'                      => 'gift-cards',
            'meta_title'                => 'Gift cards',
            'sort_order'                => 4,
            'meta_description'          => 'Order your Mall gift card online',
            'inherit_property_groups'   => true,
            'inherit_review_categories' => true,
        ]);
    }

    protected function createProperties()
    {
        $this->output->writeln('Creating properties...');
        DB::table('offline_mall_property_property_group')->truncate();
        DB::table('offline_mall_property_groups')->truncate();
        DB::table('offline_mall_properties')->truncate();

        //
        // General bike specs
        //
        $specs    = PropertyGroup::create([
            'name'         => 'Bike specs',
            'display_name' => 'Specs',
            'slug'         => 'bike-specs',
        ]);
        $gender   = Property::create([
            'name'    => 'Gender',
            'type'    => 'dropdown',
            'unit'    => '',
            'slug'    => 'gender',
            'options' => [
                ['value' => 'Male'],
                ['value' => 'Female'],
                ['value' => 'Unisex'],
            ],
        ]);
        $material = Property::create([
            'name' => 'Material',
            'type' => 'text',
            'unit' => '',
            'slug' => 'material',
        ]);
        $color    = Property::create([
            'name' => 'Color',
            'type' => 'color',
            'unit' => '',
            'slug' => 'color',
        ]);

        $this->bikePropertyGroups[] = $specs->id;
        $specs->properties()->attach([$gender->id, $material->id, $color->id], ['filter_type' => 'set']);

        //
        // Bike size
        //
        $size = PropertyGroup::create([
            'name' => 'Bikesize',
            'slug' => 'bikesize',
        ]);

        $framesize = Property::create([
            'name'    => 'Frame size',
            'type'    => 'dropdown',
            'unit'    => 'cm/inch',
            'slug'    => 'frame-size',
            'options' => [
                ['value' => 'S (38cm / 15")'],
                ['value' => 'M (43cm / 17")'],
                ['value' => 'L (48cm / 19")'],
                ['value' => 'XL (52cm / 20.5")'],
            ],
        ]);
        $wheelsize = Property::create([
            'name'    => 'Wheel size',
            'type'    => 'dropdown',
            'unit'    => 'inch',
            'slug'    => 'wheel-size',
            'options' => [
                ['value' => '26"'],
                ['value' => '27.5"'],
                ['value' => '29"'],
            ],
        ]);

        $this->bikePropertyGroups[] = $size->id;
        $size->properties()
             ->attach([$framesize->id, $wheelsize->id], ['use_for_variants' => true, 'filter_type' => 'set']);

        //
        // Suspension
        //
        $suspension = PropertyGroup::create([
            'name' => 'Suspension',
            'slug' => 'suspension',
        ]);
        $fork       = Property::create([
            'name' => 'Fork travel',
            'type' => 'integer',
            'unit' => 'mm',
            'slug' => 'fork-travel',
        ]);
        $rear       = Property::create([
            'name' => 'Rear travel',
            'type' => 'integer',
            'unit' => 'mm',
            'slug' => 'rear-travel',
        ]);

        $this->bikePropertyGroups[] = $suspension->id;
        $suspension->properties()->attach([$fork->id, $rear->id], ['filter_type' => 'range']);


        //
        // Clothes sizes
        //
        $sizeGroup = PropertyGroup::create([
            'name' => 'Size',
            'slug' => 'size',
        ]);
        $size      = Property::create([
            'name'    => 'Size',
            'type'    => 'dropdown',
            'unit'    => '',
            'slug'    => 'size',
            'options' => [
                ['value' => 'XS'],
                ['value' => 'S'],
                ['value' => 'M'],
                ['value' => 'L'],
                ['value' => 'XL'],
            ],
        ]);

        $this->clothingPropertyGroups[] = $sizeGroup->id;
        $sizeGroup->properties()->attach([$size->id], ['use_for_variants' => true, 'filter_type' => 'set']);

        //
        // Clothes specs
        //
        $specsGroup = PropertyGroup::create([
            'name'         => 'Clothing specs',
            'display_name' => 'Specs',
            'slug'         => 'specs',
        ]);

        $this->clothingPropertyGroups[] = $specsGroup->id;
        $specsGroup->properties()->attach([$color->id], ['use_for_variants' => true, 'filter_type' => 'set']);
        $specsGroup->properties()->attach([$material->id, $gender->id], ['filter_type' => 'set']);

    }

    protected function createBrands()
    {
        $this->output->writeln('Creating brands...');
        Brand::create([
            'name'        => 'Cruiser Bikes',
            'slug'        => 'cruiser-bikes',
            'description' => 'Cruiser Bikes are the leading bike manufacturer on the internet.',
            'website'     => 'https://cruiser.bikes',
            'sort_order'  => 1,
        ]);
    }

    protected function createCurrencies()
    {
        $this->output->writeln('Creating currencies...');
        DB::table('offline_mall_currencies')->truncate();
        Currency::create([
            'code'     => 'USD',
            'format'   => '{{ currency.symbol }} {{ price|number_format(2, ".", ",") }}',
            'decimals' => 2,
            'symbol'   => '$',
            'rate'     => 1.1,
        ]);
        Currency::create([
            'code'       => 'EUR',
            'format'     => '{{ price|number_format(2, " ", ",") }}{{ currency.symbol }}',
            'decimals'   => 2,
            'is_default' => true,
            'symbol'     => '€',
            'rate'       => 1,
        ]);
        Currency::create([
            'code'     => 'CHF',
            'format'   => '{{ currency.code }} {{ price|number_format(2, ".", "\'") }}',
            'decimals' => 2,
            'rate'     => 1.2,
        ]);
    }

    protected function createTaxes()
    {
        $this->output->writeln('Creating taxes...');
        DB::table('offline_mall_taxes')->truncate();
        Tax::create([
            'name'       => 'VAT',
            'percentage' => 10,
        ]);
    }

    protected function createReviewCategories()
    {
        $this->output->writeln('Creating review categories...');
        DB::table('offline_mall_review_categories')->truncate();
        ReviewCategory::create(['name' => 'Price']);
        ReviewCategory::create(['name' => 'Design']);
        ReviewCategory::create(['name' => 'Build quality']);
    }

    protected function createServices()
    {
        $this->output->writeln('Creating services...');
        DB::table('offline_mall_services')->truncate();
        DB::table('offline_mall_service_options')->truncate();

        $warranty = Service::create([
            'name'        => 'Warranty',
            'description' => 'You can extend the vendor supplied warranty for this product.',
        ]);

        $option = ServiceOption::create([
            'name'        => '2 years extended warranty',
            'description' => 'Get one additional year of warranty',
            'service_id'  => $warranty->id,
        ]);
        $option->prices()->save(new Price(['currency_id' => 2, 'price' => 49]));

        $option = ServiceOption::create([
            'name'        => '3 years extended warranty',
            'description' => 'Get two additional years of warranty',
            'service_id'  => $warranty->id,
        ]);
        $option->prices()->save(new Price(['currency_id' => 2, 'price' => 69]));

        $option = ServiceOption::create([
            'name'        => '4 years extended warranty',
            'description' => 'Get three additional years of warranty',
            'service_id'  => $warranty->id,
        ]);
        $option->prices()->save(new Price(['currency_id' => 2, 'price' => 99]));

        $assembly = Service::create([
            'name'        => 'Assembly',
            'description' => "Don't have the right tools at hand? We can preassemble this product for you.",
        ]);

        $option = ServiceOption::create([
            'name'        => 'Preassemble product',
            'description' => 'The completely assembled product will be shipped to your doorstep.',
            'service_id'  => $assembly->id,
        ]);
        $option->prices()->save(new Price(['currency_id' => 2, 'price' => 99]));

        Product::where('name', 'LIKE', 'Cruiser%')->get()->each(function (Product $product) use ($warranty, $assembly) {
            $product->services()->attach([$warranty->id, $assembly->id]);
        });
    }
}
