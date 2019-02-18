<?php namespace OFFLINE\Mall\Console;

use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use OFFLINE\Mall\Classes\Index\Index;
use OFFLINE\Mall\Classes\Index\ProductEntry;
use OFFLINE\Mall\Classes\Index\VariantEntry;
use OFFLINE\Mall\Classes\Observers\ProductObserver;
use OFFLINE\Mall\Models\Product;
use Symfony\Component\Console\Input\InputOption;

class ReindexProducts extends Command
{
    protected $name = 'mall:reindex';
    protected $description = 'Recreate the products index';
    /**
     * @var Index
     */
    protected $index;
    /**
     * @var ProductObserver
     */
    protected $observer;

    public function handle(Index $index)
    {
        $this->index = $index;

        $question = 'The current index will be dropped. Your product data will not be modified. Do you want to proceed?';
        if ( ! $this->option('force') && ! $this->output->confirm($question, false)) {
            return 0;
        }

        $this->observer = new ProductObserver($this->index);

        $this->cleanup();
        $this->reindex();
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
        $this->output->writeln('Dropping existing index...');

        $this->index->drop(ProductEntry::INDEX);
        $this->index->drop(VariantEntry::INDEX);

        $this->index->create(ProductEntry::INDEX);
        $this->index->create(VariantEntry::INDEX);
    }

    protected function reindex()
    {
        $this->output->writeln('Reindexing products...');
        $this->output->writeln('');

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
    }
}
