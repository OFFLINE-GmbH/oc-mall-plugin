<?php declare(strict_types=1);

namespace OFFLINE\Mall\Console;

use Illuminate\Console\Command;
use OFFLINE\Mall\Models\Currency;
use OFFLINE\Mall\Models\GeneralSettings;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\ShippingMethod;

class CheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'mall:check';

    /**
     * The console command name.
     * @var string
     */
    protected $name = 'mall:check';

    /**
     * The console command description.
     * @var string|null
     */
    protected $description = 'Check if your setup is complete';

    /**
     * Execute the console command.
     * @return void
     */
    public function handle()
    {
        $this->output->newLine();
        $checks = [
            [
                'title' => 'CMS pages are linked',
                'check' => fn() => $this->checkCMSPages()
            ],
            [
                'title' => 'A base currency is set',
                'check' => function () {
                    if (Currency::where('is_default', 1)->count() < 1) {
                        return 'You can set this via Backend Settings -> Mall: General -> Currencies';
                    } else {
                        return true;
                    }
                },
            ],
            [
                'title' => 'A admin e-mail is set',
                'check' => function () {
                    if (!GeneralSettings::get('admin_email')) {
                        return 'You can set this via Backend Settings -> Mall: General -> Configuration';
                    } else {
                        return true;
                    }
                },
            ],
            [
                'title' => 'All products have a price in the default currency',
                'check' => fn () => $this->checkProducts()
            ],
            [
                'title' => 'All products have a category',
                'check' => fn () => $this->checkProductCategories()
            ],
            [
                'title' => 'All shipping methods have a price in the default currency',
                'check' => fn () => $this->checkShippingMethods()
            ],
        ];

        $hints = [];
        $rows  = array_map(function ($item) use (&$hints) {
            $result = $item['check']();
            if ($result !== true) {
                $hints[] = ['title' => $item['title'], 'text' => $result];
            }

            return [$item['title'], $result === true ? 'OK' : 'FAIL'];

        }, $checks);

        $this->output->table([
            'Check',
            'Status',
        ], $rows);

        if (count($hints) < 1) {
            $this->output->newLine();
            return $this->output->success('All checks passed!');
        }

        foreach ($hints as $hint) {
            $this->output->title($hint['title']);
            $this->output->writeln($hint['text']);
            $this->output->newLine(2);
        }
    }

    /**
     * Validate all shipping methods are set up correctly.
     */
    private function checkShippingMethods()
    {
        $methods = ShippingMethod::with('prices')->get();
        $errors  = [];
        foreach ($methods as $method) {
            $basePrice = $method->prices->where('currency_id', Currency::defaultCurrency()->id)->first();
            if ( ! $basePrice) {
                $errors[] = sprintf(
                    'The shipping method "%s" has no price set for your default currency.',
                    $method->name
                );
            }
        }

        return count($errors) > 0 ? implode("\n", $errors) : true;
    }

    /**
     * Validate all shipping methods are set up correctly.
     */
    private function checkProducts()
    {
        $products = Product::with('prices')->get();
        $errors   = [];
        foreach ($products as $product) {
            $basePrice = $product->prices->where('currency_id', Currency::defaultCurrency()->id)->first();
            if ( ! $basePrice) {
                $errors[] = sprintf(
                    'The product "%s (%s)" has no price set for your default currency.',
                    $product->name,
                    $product->id
                );
            }
        }

        return count($errors) > 0 ? implode("\n", $errors) : true;
    }

    /**
     * Validate all products have a category.
     */
    private function checkProductCategories()
    {
        $products = Product::with('categories')->get();
        $errors   = [];
        foreach ($products as $product) {
            if ($product->categories->count() < 1) {
                $errors[] = sprintf(
                    'The product "%s (%s)" has no category set.',
                    $product->name,
                    $product->id
                );
            }
        }

        return count($errors) > 0 ? implode("\n", $errors) : true;
    }

    /**
     * Validate the cms pages are selected in the backend settings.
     */
    private function checkCMSPages()
    {
        $pages  = ['product_page', 'category_page', 'address_page', 'checkout_page', 'account_page'];
        $errors = [];
        foreach ($pages as $page) {
            if (GeneralSettings::get($page) === null) {
                $errors[] = '- ' . trans('offline.mall::lang.general_settings.' . $page);
            }
        }
        if (count($errors) < 1) {
            return true;
        }

        return "The following pages are not linked to a CMS page. Do this via the backend settings:\n\n" . implode("\n",
                $errors);
    }
}
