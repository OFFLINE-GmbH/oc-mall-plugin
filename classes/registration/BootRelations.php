<?php

namespace OFFLINE\Mall\Classes\Registration;

use October\Rain\Database\Relations\Relation;
use OFFLINE\Mall\Models\CustomField;
use OFFLINE\Mall\Models\CustomFieldOption;
use OFFLINE\Mall\Models\Discount;
use OFFLINE\Mall\Models\ImageSet;
use OFFLINE\Mall\Models\PaymentMethod;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\ServiceOption;
use OFFLINE\Mall\Models\ShippingMethod;
use OFFLINE\Mall\Models\ShippingMethodRate;
use OFFLINE\Mall\Models\Variant;

trait BootRelations
{

    public function registerRelations()
    {
        Relation::morphMap([
            Variant::MORPH_KEY            => Variant::class,
            Product::MORPH_KEY            => Product::class,
            ImageSet::MORPH_KEY           => ImageSet::class,
            Discount::MORPH_KEY           => Discount::class,
            CustomField::MORPH_KEY        => CustomField::class,
            PaymentMethod::MORPH_KEY      => PaymentMethod::class,
            ShippingMethod::MORPH_KEY     => ShippingMethod::class,
            CustomFieldOption::MORPH_KEY  => CustomFieldOption::class,
            ShippingMethodRate::MORPH_KEY => ShippingMethodRate::class,
            ServiceOption::MORPH_KEY      => ServiceOption::class,
        ]);
    }
}
