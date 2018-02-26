<?php

namespace OFFLINE\Mall\Models;

use Cms\Classes\Page;
use Model;
use Session;

class GeneralSettings extends Model
{
    public $implement = ['System.Behaviors.SettingsModel'];

    public $settingsCode = 'offline_mall_settings';

    public $settingsFields = '$/offline/mall/models/settings/fields_general.yaml';

    public function getProductPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('title', 'baseFileName');
    }

    public function getCategoryPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('title', 'baseFileName');
    }
}