<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Models;

use Model;

class FeedSettings extends Model
{
    public $implement = ['System.Behaviors.SettingsModel'];

    public $settingsCode = 'offline_mall_settings';

    public $settingsFields = '$/offline/mall/models/settings/fields_feeds.yaml';

    public function filterFields()
    {
        if (FeedSettings::get('google_merchant_key') === null) {
            FeedSettings::set('google_merchant_key', str_random(12));
        }
    }
}
