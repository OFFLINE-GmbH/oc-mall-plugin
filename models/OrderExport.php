<?php

namespace OFFLINE\Mall\Models;

use Backend\Models\ExportModel;

class OrderExport extends ExportModel
{
    public function exportData($columns, $sessionKey = null)
    {
        $orders = Order::all();
        $orders->map(function (Order $order) use ($columns) {
            $order->billing_address  = $order->commaSeparated($order->billing_address);
            $order->shipping_address = $order->commaSeparated($order->shipping_address);
            $order->custom_fields    = $order->commaSeparatedWithKeys($order->custom_fields);
            $order->metadata         = $order->commaSeparatedWithKeys($order->metadata);
            $order->taxes            = $order->commaSeparatedWithKeys($order->taxes);
            $order->discount_codes   = $order->getDiscountCodes();
            $order->discounts        = $order->commaSeparatedWithKeys($order->discounts);

            return $order;
        });

        return $orders->toArray();
    }
}
