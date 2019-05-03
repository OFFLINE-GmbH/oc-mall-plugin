<?php

use Cms\Classes\Controller;
use OFFLINE\Mall\Classes\Auth\BasicAuth;
use OFFLINE\Mall\Classes\Feeds\GoogleMerchantFeed;
use OFFLINE\Mall\Models\FeedSettings;

Route::get('/feeds/google-merchant', function () {

    $useFeed = FeedSettings::get('google_merchant_enabled');
    if ( ! $useFeed) {
        return (new Controller())->run('404');
    }

    $useAuth = FeedSettings::get('google_merchant_use_auth');
    if ($useAuth) {
        $username = FeedSettings::get('google_merchant_auth_username');
        $password = FeedSettings::get('google_merchant_auth_password');

        $auth = new BasicAuth($username, $password);

        if ( ! $auth->check($_SERVER['HTTP_AUTHORIZATION'] ?? '')) {
            return response('Auth failed', 401);
        }
    }

    $feed = new GoogleMerchantFeed(
        request()->get('locale')
    );

    return response($feed->build(), 200, ['Content-Type' => 'application/xml']);
});