<?php namespace OFFLINE\Mall\FormWidgets;

use Backend\Classes\FormWidgetBase;
use October\Rain\Html\Helper as Html;
use OFFLINE\Mall\Models\CurrencySettings;

/**
 * Copied from RainLab.Translate's MLText
 */
class Price extends FormWidgetBase
{
    protected $defaultCurrency;

    protected $defaultAlias = 'price';

    public function init()
    {
        $this->defaultCurrency = CurrencySettings::currencies()->first();
        $this->addJs('/plugins/offline/mall/assets/pricewidget.js', 'OFFLINE.Mall');
        $this->addCss('/plugins/offline/mall/assets/pricewidget.css', 'OFFLINE.Mall');
    }

    public function render()
    {
        $this->prepareVars();

        return $this->makePartial('price');
    }

    /**
     * Returns an array of translated values for this field
     * @return array
     */
    public function getSaveValue($value)
    {
        $values = [];
        $data   = post('MallPrice');

        $fieldName = implode('.', Html::nameToArray($this->fieldName));

        foreach ($data as $currency => $_data) {
            $value             = array_get($_data, $fieldName);
            $values[$currency] = $value;
        }

        return $values;
    }

    /**
     * Used by child classes to render in context of this view path.
     *
     * @param string $partial The view to load.
     * @param array  $params  Parameter variables to pass to the view.
     *
     * @return string The view contents.
     */
    public function makeParentPartial($partial, $params = [])
    {
        $oldViewPath    = $this->viewPath;
        $this->viewPath = $this->parentViewPath;
        $result         = $this->makePartial($partial, $params);
        $this->viewPath = $oldViewPath;

        return $result;
    }

    /**
     * Prepares the list data
     */
    public function prepareVars()
    {
        $this->vars['defaultCurrency'] = $this->defaultCurrency;
        $this->vars['defaultValue']    = $this->getPriceValue($this->defaultCurrency);
        $this->vars['currencies']      = CurrencySettings::currencies();
        $this->vars['field']           = $this->formField;
    }

    /**
     * Returns a translated value for a given currency.
     *
     * @param  string $currency
     *
     * @return string
     */
    public function getPriceValue($currency)
    {
        return $this->getLoadValue()[$currency] ?? false;
    }
}
