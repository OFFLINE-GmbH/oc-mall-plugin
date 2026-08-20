<?php

return [
    // Display order note textarea in the backend.
    'order_notes' => true,

    // Enable or disable event logging for failed payments (enabled by default)
    'log_failed_payments' => true,

    // Enable or disable OFFLINE.SiteSearch integration.
    'site_search' => env('MALL_FEATURE_SITE_SEARCH_ENABLED', true),
];
