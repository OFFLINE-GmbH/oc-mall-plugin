<?php declare(strict_types=1);

namespace OFFLINE\Mall\Updates\Seeders\Tables;

use October\Rain\Database\Updates\Seeder;
use OFFLINE\Mall\Models\PaymentMethod;

class PaymentMethodTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * @return void
     */
    public function run()
    {
        $method = new PaymentMethod();
        $method->name = 'Stripe';
        $method->payment_provider = 'stripe';
        $method->sort_order = 1;
        $method->save();
        
        $method = new PaymentMethod();
        $method->name = 'PayPal';
        $method->payment_provider = 'paypal-rest';
        $method->sort_order = 2;
        $method->save();

        $method = new PaymentMethod();
        $method->name = 'Invoice';
        $method->payment_provider = 'offline';
        $method->sort_order = 3;
        $method->save();
    }
}
