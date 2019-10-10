<?php

namespace OFFLINE\Mall\Classes\Seeders;

use October\Rain\Database\Updates\Seeder;
use OFFLINE\Mall\Models\Notification;

class NotificationTableSeeder extends Seeder
{
    public function run()
    {
        Notification::create([
            'enabled'     => true,
            'code'        => 'offline.mall::admin.checkout_succeeded',
            'name'        => 'Admin notification: Checkout succeeded',
            'description' => 'Sent to the shop admin when a checkout succeeded',
            'template'    => 'offline.mall::mail.admin.checkout_succeeded',
        ]);
        Notification::create([
            'enabled'     => true,
            'code'        => 'offline.mall::admin.checkout_failed',
            'name'        => 'Admin notification: Checkout failed',
            'description' => 'Sent to the shop admin when a checkout failed',
            'template'    => 'offline.mall::mail.admin.checkout_failed',
        ]);
        Notification::create([
            'enabled'     => true,
            'code'        => 'offline.mall::customer.created',
            'name'        => 'Customer signed up',
            'description' => 'Sent when a customer has signed up',
            'template'    => 'offline.mall::mail.customer.created',
        ]);
        Notification::create([
            'enabled'     => true,
            'code'        => 'offline.mall::checkout.succeeded',
            'name'        => 'Checkout succeeded',
            'description' => 'Sent when a checkout was successfull',
            'template'    => 'offline.mall::mail.checkout.succeeded',
        ]);
        Notification::create([
            'enabled'     => true,
            'code'        => 'offline.mall::checkout.failed',
            'name'        => 'Checkout failed',
            'description' => 'Sent when a checkout has failed',
            'template'    => 'offline.mall::mail.checkout.failed',
        ]);
        Notification::create([
            'enabled'     => true,
            'code'        => 'offline.mall::order.shipped',
            'name'        => 'Order shipped',
            'description' => 'Sent when the order has been marked as shipped',
            'template'    => 'offline.mall::mail.order.shipped',
        ]);
        Notification::create([
            'enabled'     => true,
            'code'        => 'offline.mall::order.state.changed',
            'name'        => 'Order status changed',
            'description' => 'Sent when a order status was updated',
            'template'    => 'offline.mall::mail.order.state_changed',
        ]);
        Notification::create([
            'enabled'     => true,
            'code'        => 'offline.mall::payment.paid',
            'name'        => 'Payment received',
            'description' => 'Sent when a payment has been received',
            'template'    => 'offline.mall::mail.payment.paid',
        ]);
        Notification::create([
            'enabled'     => true,
            'code'        => 'offline.mall::payment.failed',
            'name'        => 'Payment failed',
            'description' => 'Sent when a payment has failed',
            'template'    => 'offline.mall::mail.payment.failed',
        ]);
        Notification::create([
            'enabled'     => true,
            'code'        => 'offline.mall::payment.refunded',
            'name'        => 'Payment refunded',
            'description' => 'Sent when a payment has been refunded',
            'template'    => 'offline.mall::mail.payment.refunded',
        ]);
    }
}
