<?php

namespace OFFLINE\Mall\Classes\PaymentMethods;


class Stripe extends PaymentMethod
{
    public static function name(): string
    {
        return 'Stripe';
    }

    public static function identifier(): string
    {
        return 'stripe';
    }
}