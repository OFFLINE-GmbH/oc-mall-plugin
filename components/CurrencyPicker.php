<?php namespace OFFLINE\Mall\Components;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use October\Rain\Router\Router as RainRouter;
use OFFLINE\Mall\Models\Currency;

/**
 * The CurrencyPicker allows the user to select a currenty.
 */
class CurrencyPicker extends MallComponent
{
    /**
     * All available currencies.
     *
     * @var Collection
     */
    public $currencies;
    /**
     * The currently active currency.
     *
     * @var Currency
     */
    public $activeCurrency;

    /**
     * Component details.
     *
     * @return array
     */
    public function componentDetails()
    {
        return [
            'name'        => 'offline.mall::lang.components.currencyPicker.details.name',
            'description' => 'offline.mall::lang.components.currencyPicker.details.description',
        ];
    }

    /**
     * Properties of this component.
     *
     * @return array
     */
    public function defineProperties()
    {
        return [];
    }

    /**
     * The component is executed.
     *
     * @return void
     */
    public function onRun()
    {
        $this->setVar('currencies', Currency::orderBy('sort_order', 'ASC')->get());
        $this->setVar('activeCurrency', Currency::activeCurrency());
    }

    /**
     * The user selected a different currency.
     *
     * @return RedirectResponse
     */
    public function onSwitchCurrency()
    {
        if ( ! $currency = post('currency')) {
            return;
        }

        Currency::setActiveCurrency(Currency::findOrFail($currency));

        $pageUrl = $this->getUrl();

        // preserve the query string, if it exists
        $query   = http_build_query(request()->query());
        $pageUrl = $query ? $pageUrl . '?' . $query : $pageUrl;

        return redirect()->to($pageUrl);
    }

    /**
     * Return the URL of the current page.
     *
     * Handle static and cms pages.
     *
     * @return string
     */
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
