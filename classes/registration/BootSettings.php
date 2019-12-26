<?php

namespace OFFLINE\Mall\Classes\Registration;

use Backend\Facades\Backend;
use OFFLINE\Mall\Models\FeedSettings;
use OFFLINE\Mall\Models\GeneralSettings;
use OFFLINE\Mall\Models\PaymentGatewaySettings;
use OFFLINE\Mall\Models\ReviewSettings;

trait BootSettings
{
    public function registerSettings()
    {
        return [
            'general_settings'          => [
                'label'       => 'offline.mall::lang.general_settings.label',
                'description' => 'offline.mall::lang.general_settings.description',
                'category'    => 'offline.mall::lang.general_settings.category',
                'icon'        => 'icon-shopping-cart',
                'class'       => GeneralSettings::class,
                'order'       => 0,
                'permissions' => ['offline.mall.settings.manage_general'],
                'keywords'    => 'shop store mall general',
            ],
            'currency_settings'         => [
                'label'       => 'offline.mall::lang.currency_settings.label',
                'description' => 'offline.mall::lang.currency_settings.description',
                'category'    => 'offline.mall::lang.general_settings.category',
                'icon'        => 'icon-money',
                'url'         => Backend::url('offline/mall/currencies'),
                'order'       => 20,
                'permissions' => ['offline.mall.settings.manage_currency'],
                'keywords'    => 'shop store mall currency',
            ],
            'price_categories_settings' => [
                'label'       => 'offline.mall::lang.price_category_settings.label',
                'description' => 'offline.mall::lang.price_category_settings.description',
                'category'    => 'offline.mall::lang.general_settings.category',
                'icon'        => 'icon-pie-chart',
                'url'         => Backend::url('offline/mall/pricecategories'),
                'order'       => 20,
                'permissions' => ['offline.mall.settings.manage_price_categories'],
                'keywords'    => 'shop store mall currency price categories',
            ],
            'tax_settings'              => [
                'label'       => 'offline.mall::lang.common.taxes',
                'description' => 'offline.mall::lang.tax_settings.description',
                'category'    => 'offline.mall::lang.general_settings.category',
                'icon'        => 'icon-percent',
                'url'         => Backend::url('offline/mall/taxes'),
                'order'       => 40,
                'permissions' => ['offline.mall.manage_taxes'],
                'keywords'    => 'shop store mall tax taxes',
            ],
            'notification_settings'     => [
                'label'       => 'offline.mall::lang.notification_settings.label',
                'description' => 'offline.mall::lang.notification_settings.description',
                'category'    => 'offline.mall::lang.general_settings.category',
                'icon'        => 'icon-envelope',
                'url'         => Backend::url('offline/mall/notifications'),
                'order'       => 40,
                'permissions' => ['offline.mall.manage_notifications'],
                'keywords'    => 'shop store mall notifications email mail',
            ],
            'feed_settings'             => [
                'label'       => 'offline.mall::lang.common.feeds',
                'description' => 'offline.mall::lang.feed_settings.description',
                'category'    => 'offline.mall::lang.general_settings.category',
                'icon'        => 'icon-rss',
                'class'       => FeedSettings::class,
                'order'       => 50,
                'permissions' => ['offline.mall.manage_feeds'],
                'keywords'    => 'shop store mall feeds',
            ],
            'review_settings'           => [
                'label'       => 'offline.mall::lang.common.reviews',
                'description' => 'offline.mall::lang.review_settings.description',
                'category'    => 'offline.mall::lang.general_settings.category',
                'icon'        => 'icon-star',
                'class'       => ReviewSettings::class,
                'order'       => 60,
                'permissions' => ['offline.mall.manage_reviews'],
                'keywords'    => 'shop store mall reviews',
            ],
            'payment_gateways_settings' => [
                'label'       => 'offline.mall::lang.payment_gateway_settings.label',
                'description' => 'offline.mall::lang.payment_gateway_settings.description',
                'category'    => 'offline.mall::lang.general_settings.category_payments',
                'icon'        => 'icon-credit-card',
                'class'       => PaymentGatewaySettings::class,
                'order'       => 30,
                'permissions' => ['offline.mall.settings.manage_payment_gateways'],
                'keywords'    => 'shop store mall payment gateways',
            ],
            'payment_method_settings'   => [
                'label'       => 'offline.mall::lang.common.payment_methods',
                'description' => 'offline.mall::lang.payment_method_settings.description',
                'category'    => 'offline.mall::lang.general_settings.category_payments',
                'icon'        => 'icon-money',
                'url'         => Backend::url('offline/mall/paymentmethods'),
                'order'       => 40,
                'permissions' => ['offline.mall.settings.manage_payment_methods'],
                'keywords'    => 'shop store mall payment methods',
            ],
            'shipping_method_settings'  => [
                'label'       => 'offline.mall::lang.common.shipping_methods',
                'description' => 'offline.mall::lang.shipping_method_settings.description',
                'category'    => 'offline.mall::lang.general_settings.category_orders',
                'icon'        => 'icon-truck',
                'url'         => Backend::url('offline/mall/shippingmethods'),
                'order'       => 40,
                'permissions' => ['offline.mall.manage_shipping_methods'],
                'keywords'    => 'shop store mall shipping methods',
            ],
            'order_state_settings'      => [
                'label'       => 'offline.mall::lang.common.order_states',
                'description' => 'offline.mall::lang.order_state_settings.description',
                'category'    => 'offline.mall::lang.general_settings.category_orders',
                'icon'        => 'icon-history',
                'url'         => Backend::url('offline/mall/orderstate'),
                'order'       => 50,
                'permissions' => ['offline.mall.manage_order_states'],
                'keywords'    => 'shop store mall notifications email mail',
            ],
        ];
    }
}
