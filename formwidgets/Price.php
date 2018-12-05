<?php namespace OFFLINE\Mall\FormWidgets;

use Backend\Classes\FormWidgetBase;
use October\Rain\Exception\ValidationException;
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
     * The price widget's form values have to be handled manually
     * in the controller since the prices might go to different models
     * and different fields at once.
     *
     * This mode is "misused" to add basic validation to make sure that
     * at least one price in the default currency is provided if the
     * field's required attribute is set to true.
     *
     * @return array
     */
    public function getSaveValue($value)
    {
        if ($this->formField->required !== true) {
            return null;
        }

        $values = collect(post('MallPrice'))->map(function ($value, $key) {
            if ($value[$this->valueFrom] === '' || $value[$this->valueFrom] === null) {
                return null;
            }

            return $key;
        })->filter();

        if ( ! $values->has($this->defaultCurrency->id)) {
            throw new ValidationException([$this->valueFrom => trans('offline.mall::lang.common.price_missing')]);
        }

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

        if ( ! $this->model->hasRelation($relation)) {
            return null;
        }

        $this->model->loadMissing($relation);

        return $this->model->getRelation($relation);
    }
}
