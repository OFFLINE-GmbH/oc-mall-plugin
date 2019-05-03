<?php

namespace OFFLINE\Mall\Models;

use Model;
use Session;

class FeedSettings extends Model
{
    public $implement = ['System.Behaviors.SettingsModel'];
    public $settingsCode = 'offline_mall_settings';
    public $settingsFields = '$/offline/mall/models/settings/fields_feeds.yaml';
}
