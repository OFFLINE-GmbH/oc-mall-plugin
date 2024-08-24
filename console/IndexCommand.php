<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Console;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use OFFLINE\Mall\Classes\Index\Index;
use OFFLINE\Mall\Classes\Index\ProductEntry;
use OFFLINE\Mall\Classes\Index\VariantEntry;
use OFFLINE\Mall\Classes\Observers\ProductObserver;
use OFFLINE\Mall\Models\Product;

class IndexCommand extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = '
        mall:index 
        {--force : Don\'t ask before erasing the index records} 
    ';

    /**
     * The console command name.
     * @var string
     */
    protected $name = 'mall:index';

    /**
     * The console command description.
     * @var string|null
     */
    protected $description = 'Recreate the general product index';

    /**
     * @var Index
     */
    protected $index;

    /**
     * @var ProductObserver
     */
    protected $observer;

    /**
     * Execute the console command.
     * @return void
     */
    public function handle(Index $index)
    {
        $this->index = $index;

        $question = 'The current index will be dropped. Your product data will not be modified. Do you want to proceed?';

        if (!$this->option('force') && ! $this->output->confirm($question, false)) {
            return 0;
        }

        $this->observer = new ProductObserver($this->index);

        // Clean Index
        $this->warn(' Purge and Create Index...');

        try {
            $this->cleanup();
            $this->info('Re-Created Index successful.');
        } catch (Exception $exc) {
            $this->output->block('The following error occurred.', 'ERROR', 'fg=red');
            $this->error($exc->getMessage());

            return 0;
        }
        $this->output->newLine();

        // Re-Index Products
        $this->warn(' Index Products...');

        try {
            $this->reindex();
            $this->info('Indexing successful.');
        } catch (Exception $exc) {
            $this->output->block('The following error occurred.', 'ERROR', 'fg=red');
            $this->error($exc->getMessage());

            return 0;
        }
        $this->output->newLine();

        // Finish
        $this->alert('Index has been created!');
    }

    /**
     * Cleanup Index
     * @return void
     */
    protected function cleanup()
    {
        $this->index->drop(ProductEntry::INDEX);
        $this->index->drop(VariantEntry::INDEX);
        $this->index->create(ProductEntry::INDEX);
        $this->index->create(VariantEntry::INDEX);
    }

    /**
     * Create Index
     * @return void
     */
    protected function reindex()
    {
        $bar = $this->output->createProgressBar(Product::count());
        Product::orderBy('id')->with([
            'variants.prices.currency',
            'prices.currency',
            'property_values.property',
            'categories',
            'variants.prices.currency',
            'variants.property_values.property',
        ])->chunk(200, function (Collection $products) use ($bar) {
            $products->each(function (Product $product) use ($bar) {
                $this->observer->created($product);
                $bar->advance();
            });
        });
        $bar->finish();
        $bar->clear();
    }
}
