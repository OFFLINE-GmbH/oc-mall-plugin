<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Updates\Seeders\Tables;

use October\Rain\Database\Updates\Seeder;
use OFFLINE\Mall\Models\Notification;

class NotificationTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * @param bool $useDemo
     * @return void
     */
    public function run(bool $useDemo = false)
    {
        if ($useDemo) {
            return;
        }
        
        Notification::create([
            'enabled'     => true,
            'code'        => 'offline.mall::admin.checkout_succeeded',
            'name'        => trans('offline.mall::demo.notifications.admin_checkout_succeeded.name'),
            'description' => trans('offline.mall::demo.notifications.admin_checkout_succeeded.description'),
            'template'    => 'offline.mall::mail.admin.checkout_succeeded',
        ]);

        Notification::create([
            'enabled'     => true,
            'code'        => 'offline.mall::admin.checkout_failed',
            'name'        => trans('offline.mall::demo.notifications.admin_checkout_failed.name'),
            'description' => trans('offline.mall::demo.notifications.admin_checkout_failed.description'),
            'template'    => 'offline.mall::mail.admin.checkout_failed',
        ]);

        Notification::create([
            'enabled'     => true,
            'code'        => 'offline.mall::customer.created',
            'name'        => trans('offline.mall::demo.notifications.customer_created.name'),
            'description' => trans('offline.mall::demo.notifications.customer_created.description'),
            'template'    => 'offline.mall::mail.customer.created',
        ]);

        Notification::create([
            'enabled'     => true,
            'code'        => 'offline.mall::checkout.succeeded',
            'name'        => trans('offline.mall::demo.notifications.checkout_succeeded.name'),
            'description' => trans('offline.mall::demo.notifications.checkout_succeeded.description'),
            'template'    => 'offline.mall::mail.checkout.succeeded',
        ]);

        Notification::create([
            'enabled'     => true,
            'code'        => 'offline.mall::checkout.failed',
            'name'        => trans('offline.mall::demo.notifications.checkout_failed.name'),
            'description' => trans('offline.mall::demo.notifications.checkout_failed.description'),
            'template'    => 'offline.mall::mail.checkout.failed',
        ]);

        Notification::create([
            'enabled'     => true,
            'code'        => 'offline.mall::order.shipped',
            'name'        => trans('offline.mall::demo.notifications.order_shipped.name'),
            'description' => trans('offline.mall::demo.notifications.order_shipped.description'),
            'template'    => 'offline.mall::mail.order.shipped',
        ]);

        Notification::create([
            'enabled'     => true,
            'code'        => 'offline.mall::order.state.changed',
            'name'        => trans('offline.mall::demo.notifications.order_state_changed.name'),
            'description' => trans('offline.mall::demo.notifications.order_state_changed.description'),
            'template'    => 'offline.mall::mail.order.state_changed',
        ]);

        Notification::create([
            'enabled'     => true,
            'code'        => 'offline.mall::payment.paid',
            'name'        => trans('offline.mall::demo.notifications.payment_paid.name'),
            'description' => trans('offline.mall::demo.notifications.payment_paid.description'),
            'template'    => 'offline.mall::mail.payment.paid',
        ]);

        Notification::create([
            'enabled'     => true,
            'code'        => 'offline.mall::payment.failed',
            'name'        => trans('offline.mall::demo.notifications.payment_failed.name'),
            'description' => trans('offline.mall::demo.notifications.payment_failed.description'),
            'template'    => 'offline.mall::mail.payment.failed',
        ]);

        Notification::create([
            'enabled'     => true,
            'code'        => 'offline.mall::payment.refunded',
            'name'        => trans('offline.mall::demo.notifications.payment_refunded.name'),
            'description' => trans('offline.mall::demo.notifications.payment_refunded.description'),
            'template'    => 'offline.mall::mail.payment.refunded',
        ]);
    }
}
