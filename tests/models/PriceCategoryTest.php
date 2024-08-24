<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Tests\Models;

use Illuminate\Support\Facades\DB;
use OFFLINE\Mall\Classes\User\Auth;
use OFFLINE\Mall\Models\Currency;
use OFFLINE\Mall\Models\Price;
use OFFLINE\Mall\Models\PriceCategory;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Tests\PluginTestCase;
use RainLab\User\Models\User;

class PriceCategoryTest extends PluginTestCase
{
    /**
     * Setup the test environment.
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        Auth::login(User::first());
    }

    /**
     * Create and receive additional prices.
     * @return void
     */
    public function test_create_additional_prices()
    {
        $this->prepare();

        // Fetch prices, added by the prepare method.
        $product = Product::first();
        $prices = $product->additional_prices()->get()->mapWithKeys(
            fn ($price) => [$price->category->code => $price->float]
        )->toArray();

        // Check if values has been added correctly.
        $this->assertEquals([
            'old_price' => 50.0,
            'msrp' => 60.0,
        ], $prices);
    }

    /**
     * Return only enabled price categories.
     * @return void
     */
    public function test_return_only_enabled_prices_unless_with_disabled_is_used()
    {
        $this->prepare();

        // Disable MSRP price category.
        $msrpPriceCategory = PriceCategory::where('code', 'msrp')->first();
        $msrpPriceCategory->is_enabled = false;
        $msrpPriceCategory->save();

        // Fetch prices, added by the prepare method.
        $product = Product::first();
        $prices = $product->additional_prices()->get()->mapWithKeys(fn (Price $price) => [$price->category->code => $price->float])->toArray();

        // Should only contain old_price, since msrp has been disabled.
        $this->assertEquals([
            'old_price' => 50.0,
        ], $prices);

        // Receive all prices by especially adding "withDisabled" scope.
        $prices = $product->additional_prices()->withDisabled()->get()->mapWithKeys(fn (Price $price) => [$price->category->code => $price->float])->toArray();
        $this->assertEquals([
            'old_price' => 50.0,
            'msrp' => 60.0,
        ], $prices);
    }

    /**
     * Delete prices from each price category, even disabled ones.
     * @return void
     */
    public function test_delete_all_prices_even_disabled_ones()
    {
        $this->prepare();

        // Disable MSRP price category.
        $msrpPriceCategory = PriceCategory::where('code', 'msrp')->first();
        $msrpPriceCategory->is_enabled = false;
        $msrpPriceCategory->save();

        // Delete Prices, even from disabled price categories
        $product = Product::first();
        $product->additional_prices()->withDisabled()->delete();

        // Check if prices has been deleted
        $prices = DB::table('offline_mall_prices')
            ->where('priceable_type', Product::MORPH_KEY)
            ->where('priceable_id', $product->id)
            ->count();
        $this->assertEquals($prices, 0);
    }

    /**
     * Prepare Tests below
     * @return void
     */
    protected function prepare()
    {
        $product = Product::first();
        $currency = Currency::where('code', 'CHF')->first();
        $oldPriceCategory = PriceCategory::where('code', 'old_price')->first();
        $msrpPriceCategory = PriceCategory::where('code', 'msrp')->first();

        $product->additional_prices()->save(new Price([
            'price'             => 50,
            'price_category_id' => $oldPriceCategory->id,
            'currency_id'       => $currency->id,
        ]));
        $product->additional_prices()->save(new Price([
            'price'             => 60,
            'price_category_id' => $msrpPriceCategory->id,
            'currency_id'       => $currency->id,
        ]));
    }
}
