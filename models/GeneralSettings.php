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
        return $this->getPages();
    }

    public function getCategoryPageOptions()
    {
        return $this->getPages();
    }

    public function getAddressPageOptions()
    {
        return $this->getPages();
    }

    public function getCheckoutPageOptions()
    {
        return $this->getPages();
    }

    protected function getPages()
    {
        return Page::sortBy('baseFileName')->lists('title', 'baseFileName');
    }
}