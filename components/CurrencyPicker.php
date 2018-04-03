<?php namespace OFFLINE\Mall\Components;

use October\Rain\Router\Router as RainRouter;
use OFFLINE\Mall\Models\CurrencySettings;
use Redirect;

class CurrencyPicker extends MallComponent
{
    public $currencies;
    public $activeCurrency;

    public function componentDetails()
    {
        return [
            'name'        => 'offline.mall::lang.components.currencyPicker.details.name',
            'description' => 'offline.mall::lang.components.currencyPicker.details.description',
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        $this->setVar('currencies', CurrencySettings::get('currencies'));
        $this->setVar('activeCurrency', CurrencySettings::activeCurrency());
    }

    public function onSwitchCurrency()
    {
        if ( ! $currency = post('currency')) {
            return;
        }

        CurrencySettings::setActiveCurrency($currency);

        $pageUrl = $this->getUrl();

        // preserve the query string, if it exists
        $query   = http_build_query(request()->query());
        $pageUrl = $query ? $pageUrl . '?' . $query : $pageUrl;

        return Redirect::to($pageUrl);
    }

    protected function getUrl()
    {
        $page = $this->getPage();

        if (isset($page->apiBag['staticPage'])) {
            $staticPage = $page->apiBag['staticPage'];
            $localeUrl  = array_get($staticPage->attributes, 'viewBag.url');
        } else {
            $router    = new RainRouter;
            $params    = $this->getRouter()->getParameters();
            $localeUrl = $router->urlFromPattern($page->url, $params);
        }

        return $localeUrl;
    }
}
