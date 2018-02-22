<?php namespace OFFLINE\Mall\FormWidgets;

use Backend\Classes\FormField;
use Backend\Classes\FormWidgetBase;
use Backend\FormWidgets\ColorPicker;
use Backend\FormWidgets\FileUpload;
use OFFLINE\Mall\Models\Property;
use OFFLINE\Mall\Models\PropertyValue;

/**
 * PropertyFields Form Widget
 */
class PropertyFields extends FormWidgetBase
{
    /**
     * {@inheritDoc}
     */
    protected $defaultAlias = 'propertyfields';

    /**
     * {@inheritDoc}
     */
    public function render()
    {
        $this->prepareVars();

        return $this->makePartial('propertyfields');
    }

    /**
     * Prepares the form widget view data
     */
    public function prepareVars()
    {
        $this->vars['name']   = $this->formField->getName();
        $this->vars['values'] = $this->model->property_values ?? collect([]);
        $this->vars['model']  = $this->model;

        $fields = optional($this->controller->vars['formModel']->category)->properties;

        if ($this->useVariantSpecificPropertiesOnly()) {
            $fields = $fields->filter(function (Property $property) {
                return (bool)$property->pivot->use_for_variants === true;
            });
        }

        $this->vars['fields'] = $fields;
    }

    public function createFormWidget(Property $property, $value)
    {
        switch ($property->type) {
            case 'color':
                return $this->color($property, $value);
            case 'textarea':
                return $this->textarea($property, $value);
            case 'dropdown':
                return $this->dropdown($property, $value);
            case 'checkbox':
                return $this->checkbox($property, $value);
            case 'image':
                return $this->image($property, $value);
            default:
                return $this->textfield($property, $value);
        }
    }

    private function color($property, $value)
    {
        $config = $this->makeConfig([
            'model' => new PropertyValue(),
        ]);

        $formField        = $this->newFormField($property);
        $formField->value = $value;

        $widget             = new ColorPicker($this->controller, $formField, $config);
        $widget->allowEmpty = true;
        $widget->bindToController();

        return $this->makePartial('colorpicker', ['field' => $property, 'widget' => $widget, 'value' => $value]);
    }

    private function textfield($property, $value)
    {
        return $this->makePartial('textfield', ['field' => $property, 'value' => $value]);
    }

    private function textarea($property, $value)
    {
        return $this->makePartial('textarea', ['field' => $property, 'value' => $value]);
    }

    private function dropdown($property, $value)
    {
        $formField          = $this->newFormField($property);
        $formField->value   = $value;
        $formField->label   = $property->name;
        $formField->options = collect($property->options)->mapWithKeys(function ($i) {
            return [$i['value'] => $i['value']];
        })->toArray();

        $widget = $this->makePartial('modules/backend/widgets/form/partials/field_dropdown',
            ['field' => $formField, 'value' => $value]
        );

        return $this->makePartial('dropdown', ['widget' => $widget, 'field' => $property]);
    }

    private function checkbox($property, $value)
    {
        $formField          = $this->newFormField($property);
        $formField->value   = $value;
        $formField->label   = $property->name;
        $formField->options = collect($property->options)->map(function ($i) {
            return [$i['value'], $i['value']];
        })->toArray();

        return $this->makePartial('modules/backend/widgets/form/partials/field_checkbox',
            ['field' => $formField, 'value' => $value]
        );
    }

    private function image($property, $value)
    {
        $config = $this->makeConfig([
            'model'      => optional($this->model->property_values->where('property_id', $property->id))
                    ->first() ?? new PropertyValue(),
            'sessionKey' => $this->sessionKey,
        ]);

        $formField            = $this->newFormField($property);
        $formField->valueFrom = 'image';

        $widget        = new FileUpload($this->controller, $formField, $config);
        $widget->alias = 'image';
        $widget->bindToController();

        return $this->makePartial('fileupload',
            ['field' => $property, 'widget' => $widget, 'value' => $value, 'session_key' => $this->sessionKey]);
    }

    protected function newFormField($property): FormField
    {
        return new FormField($this->fieldPrefix() . '[' . $property->id . ']', $property->name);
    }

    protected function useVariantSpecificPropertiesOnly(): bool
    {
        return isset($this->formField->config['variantPropertiesOnly']) && $this->formField->config['variantPropertiesOnly'] === true;
    }

    protected function fieldPrefix(): string
    {
        return $this->formField->config['fieldPrefix'] ?? 'PropertyValues';
    }

}
