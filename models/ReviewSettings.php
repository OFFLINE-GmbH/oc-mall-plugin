<?php

namespace OFFLINE\Mall\Models;

use Model;
use Session;

class ReviewSettings extends Model
{
    public $implement = ['System.Behaviors.SettingsModel'];
    public $settingsCode = 'offline_mall_settings';
    public $settingsFields = '$/offline/mall/models/settings/fields_reviews.yaml';

    public function initSettingsData()
    {
        $this->enabled          = true;
        $this->allow_non_buyers = true;
        $this->allow_anonymous  = false;
    }
}
