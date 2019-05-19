<?php

namespace OFFLINE\Mall\Classes\Registration;

use OFFLINE\Mall\Components\AddressForm;
use OFFLINE\Mall\Components\AddressList;
use OFFLINE\Mall\Components\AddressSelector;
use OFFLINE\Mall\Components\Cart;
use OFFLINE\Mall\Components\Checkout;
use OFFLINE\Mall\Components\CurrencyPicker;
use OFFLINE\Mall\Components\CustomerProfile;
use OFFLINE\Mall\Components\DiscountApplier;
use OFFLINE\Mall\Components\EnhancedEcommerceAnalytics;
use OFFLINE\Mall\Components\MallDependencies;
use OFFLINE\Mall\Components\MyAccount;
use OFFLINE\Mall\Components\OrdersList;
use OFFLINE\Mall\Components\PaymentMethodSelector;
use OFFLINE\Mall\Components\Product as ProductComponent;
use OFFLINE\Mall\Components\Products as ProductsComponent;
use OFFLINE\Mall\Components\ProductsFilter;
use OFFLINE\Mall\Components\ShippingMethodSelector;
use OFFLINE\Mall\Components\SignUp;
use OFFLINE\Mall\FormWidgets\Price;
use OFFLINE\Mall\FormWidgets\PropertyFields;

trait BootComponents
{
    public function registerComponents()
    {
        return [
            Cart::class                       => 'cart',
            SignUp::class                     => 'signUp',
            ShippingMethodSelector::class     => 'shippingMethodSelector',
            AddressSelector::class            => 'addressSelector',
            AddressForm::class                => 'addressForm',
            PaymentMethodSelector::class      => 'paymentMethodSelector',
            Checkout::class                   => 'checkout',
            ProductsComponent::class          => 'products',
            ProductsFilter::class             => 'productsFilter',
            ProductComponent::class           => 'product',
            DiscountApplier::class            => 'discountApplier',
            MyAccount::class                  => 'myAccount',
            OrdersList::class                 => 'ordersList',
            CustomerProfile::class            => 'customerProfile',
            AddressList::class                => 'addressList',
            CurrencyPicker::class             => 'currencyPicker',
            MallDependencies::class           => 'mallDependencies',
            EnhancedEcommerceAnalytics::class => 'enhancedEcommerceAnalytics',
        ];
    }

    public function registerFormWidgets()
    {
        return [
            PropertyFields::class => 'mall.propertyfields',
            Price::class          => 'mall.price',
        ];
    }

    protected function bootComponents()
    {
    }
}
