<?php

namespace OFFLINE\Mall\Classes\Seeders;

use October\Rain\Database\Updates\Seeder;
use OFFLINE\Mall\Models\PaymentMethod;

class PaymentMethodTableSeeder extends Seeder
{
    public function run()
    {
        $method                   = new PaymentMethod();
        $method->name             = 'Stripe';
        $method->price            = 0;
        $method->payment_provider = 'stripe';
        $method->sort_order       = 1;
        $method->save();
        
        $method                   = new PaymentMethod();
        $method->name             = 'PayPal';
        $method->price            = 0;
        $method->payment_provider = 'paypal-rest';
        $method->sort_order       = 2;
        $method->save();
    }
}
