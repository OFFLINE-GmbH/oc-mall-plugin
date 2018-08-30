<?php namespace OFFLINE\Mall\FormWidgets;

use Backend\Classes\FormWidgetBase;
use OFFLINE\Mall\Models\Currency;

/**
 * Copied from RainLab.Translate's MLText
 */
class Price extends FormWidgetBase
{
    protected $defaultCurrency;

    protected $defaultAlias = 'price';

    public function init()
    {
        $this->defaultCurrency = Currency::orderBy('is_default', 'DESC')->first();
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
        return null;
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
        $this->vars['defaultValue']    = $this->getPriceValue($this->defaultCurrency->id);
        $this->vars['currencies']      = Currency::orderBy('sort_order', 'ASC')->get();
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
        $value = $this->getLoadValue();
        if ( ! $value) {
            return null;
        }

        return $value->where('currency_id', $currency)->first()->decimal ?? false;
    }

    public function getLoadValue()
    {
        $relation = ltrim($this->valueFrom, '_');

        if ($this->model->relationLoaded($relation)) {
            return $this->model->getRelation($relation);
        }

        return null;
    }
}
