<?php namespace OFFLINE\Mall\FormWidgets;

use Backend\Classes\FormWidgetBase;

/**
 * VariantSelector Form Widget
 */
class VariantSelector extends FormWidgetBase
{
    /**
     * {@inheritDoc}
     */
    protected $defaultAlias = 'variantselector';

    /**
     * {@inheritDoc}
     */
    public function render()
    {
        $this->prepareVars();

        return $this->makePartial('variantselector');
    }

    /**
     * Prepares the form widget view data
     */
    public function prepareVars()
    {
        $this->vars['name']   = $this->formField->getName();
        $this->vars['value']  = $this->getLoadValue();
        $this->vars['model']  = $this->model;
        $this->vars['fields'] = $this->controller->vars['formModel']->variant_options;
    }

    /**
     * {@inheritDoc}
     */
    public function getLoadValue()
    {
        return $this->model->custom_field_options;
    }

}
