<?php namespace OFFLINE\Mall\FormWidgets;

use Backend\Classes\FormField;
use Backend\Classes\FormWidgetBase;
use Backend\FormWidgets\ColorPicker;
use Backend\FormWidgets\DatePicker;
use Backend\FormWidgets\FileUpload;
use Backend\FormWidgets\RichEditor;
use OFFLINE\Mall\Models\Property;
use OFFLINE\Mall\Models\PropertyGroup;
use OFFLINE\Mall\Models\PropertyValue;
use Svg\Tag\Group;

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

        $groups = optional(
            $this->controller->vars['formModel']
                ->categories
                ->flatMap->inherited_property_groups
        )->unique('id');

        if ($this->controller->vars['formModel']->inventory_management_method !== 'single') {
            $useForVariants = $this->useVariantSpecificPropertiesOnly();
            $groups         = PropertyGroup::with([
                'properties' => function ($q) use ($useForVariants) {
                    $q->wherePivot('use_for_variants', $useForVariants);
                },
            ])->whereIn('id', $groups->pluck('id'))->get();
        }

        // Make sure every property is only displayed once even if it is
        // assigned to more than one property group.
        $knownIds = collect([]);
        $groups = $groups->map(function (PropertyGroup $group) use (&$knownIds) {
            $unknownIds = $group->properties->pluck('id')->diff($knownIds);
            $knownIds = $knownIds->concat($unknownIds);

            $properties = $group->properties->filter(function ($property) use ($unknownIds) {
                return $unknownIds->contains($property->id);
            });

            $group->setRelation('properties', $properties);

            return $group;
        });

        $this->vars['groups'] = $groups->sortBy('pivot.sort_order');
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
            case 'richeditor':
                return $this->richeditor($property, $value);
            case 'date':
                return $this->datepicker($property, $value);
            case 'datetime':
                return $this->datetimepicker($property, $value);
            case 'image':
                return $this->image($property, $value);
            case 'float':
            case 'integer':
                return $this->textfield($property, $value, 'number');
            default:
                return $this->textfield($property, $value);
        }
    }

    private function color($property, $value)
    {
        $config = $this->makeConfig([
            'model' => new PropertyValue(),
        ]);

        $formField        = $this->newFormField($property, 'hex');
        $formField->value = $value['hex'] ?? '';

        $widget             = new ColorPicker($this->controller, $formField, $config);
        $widget->allowEmpty = true;
        $widget->bindToController();

        return $this->makePartial(
            'colorpicker',
            ['field' => $property, 'widget' => $widget, 'value' => $value]
        );
    }

    private function textfield($property, $value, $type = 'text')
    {
        return $this->makePartial('textfield', ['field' => $property, 'value' => $value, 'type' => $type]);
    }

    private function textarea($property, $value)
    {
        return $this->makePartial('textarea', ['field' => $property, 'value' => $value]);
    }

    private function richeditor($property, $value)
    {
        $config = $this->makeConfig([
            'model' => new PropertyValue(),
        ]);

        $formField        = $this->newFormField($property);
        $formField->value = $value;

        $widget             = new RichEditor($this->controller, $formField, $config);
        $widget->allowEmpty = true;
        $widget->bindToController();

        return $this->makePartial(
            'richeditor',
            ['field' => $property, 'widget' => $widget, 'value' => $value]
        );
    }

    private function datetimepicker($property, $value)
    {
        $config = $this->makeConfig([
            'model' => new PropertyValue(),
        ]);

        $formField        = $this->newFormField($property);
        $formField->value = $value;

        $widget             = new DatePicker($this->controller, $formField, $config);
        $widget->allowEmpty = true;
        $widget->bindToController();

        return $this->makePartial(
            'datetimepicker',
            ['field' => $property, 'widget' => $widget, 'value' => $value]
        );
    }

    private function datepicker($property, $value)
    {
        $config = $this->makeConfig([
            'model' => new PropertyValue(),
        ]);

        $formField        = $this->newFormField($property);
        $formField->value = $value;

        $widget             = new DatePicker($this->controller, $formField, $config);
        $widget->allowEmpty = true;
        $widget->mode = 'date';
        $widget->bindToController();

        return $this->makePartial(
            'datepicker',
            ['field' => $property, 'widget' => $widget, 'value' => $value]
        );
    }

    private function dropdown($property, $value)
    {
        $value = e($value);

        $formField          = $this->newFormField($property);
        $formField->value   = $value;
        $formField->label   = $property->name;
        $formField->options = collect($property->options)->mapWithKeys(function ($i) {
            $value = e($i['value']);

            return [$value => $value];
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

    protected function newFormField($property, $subkey = null): FormField
    {
        $subkey    = $subkey ? '[' . $subkey . ']' : '';
        $fieldName = sprintf('%s[%s]%s', $this->fieldPrefix(), $property->id, $subkey);

        return new FormField($fieldName, $property->name);
    }

    protected function useVariantSpecificPropertiesOnly(): bool
    {
        return isset($this->formField->config['variantPropertiesOnly']) && $this->formField->config['variantPropertiesOnly'] === true;
    }

    public function fieldPrefix(): string
    {
        return $this->formField->config['fieldPrefix'] ?? 'PropertyValues';
    }

}
