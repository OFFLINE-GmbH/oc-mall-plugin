<?php

namespace OFFLINE\Mall\Classes\Registration;


trait BootMails
{

    public function registerMailTemplates()
    {
        return [
            'offline.mall::mail.customer.created',
            'offline.mall::mail.order.state_changed',
            'offline.mall::mail.order.shipped',
            'offline.mall::mail.checkout.succeeded',
            'offline.mall::mail.checkout.failed',
            'offline.mall::mail.payment.failed',
            'offline.mall::mail.payment.paid',
            'offline.mall::mail.payment.refunded',
        ];
    }

    public function registerMailPartials()
    {
        return [
            'mall.order.table'    => 'offline.mall::mail._partials.order.table',
            'mall.order.tracking' => 'offline.mall::mail._partials.order.tracking',
        ];
    }
}