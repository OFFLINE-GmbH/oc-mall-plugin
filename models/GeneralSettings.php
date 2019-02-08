<?php

namespace OFFLINE\Mall\Models;

use Cms\Classes\Page;
use Illuminate\Support\Facades\Cache;
use Model;
use Session;

class GeneralSettings extends Model
{
    public $implement = ['System.Behaviors.SettingsModel'];
    public $settingsCode = 'offline_mall_settings';
    public $settingsFields = '$/offline/mall/models/settings/fields_general.yaml';

    public function afterSave()
    {
        Cache::forget('offline_mall.mysql.index.driver');
    }

    public function getPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('title', 'baseFileName');
    }
}
