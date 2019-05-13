<?php

use Cms\Classes\Controller;
use OFFLINE\Mall\Classes\Feeds\GoogleMerchantFeed;
use OFFLINE\Mall\Models\FeedSettings;

Route::get('/feeds/google-merchant/{key}', function ($key) {
    $useFeed = FeedSettings::get('google_merchant_enabled');
    if ( ! $useFeed) {
        return (new Controller())->run('404');
    }

    $settingsKey = FeedSettings::get('google_merchant_key');
    if ($key !== $settingsKey) {
        return response('Auth failed', 401);
    }

    $feed = new GoogleMerchantFeed(
        request()->get('locale')
    );

    return response($feed->build(), 200, ['Content-Type' => 'application/xml']);
});