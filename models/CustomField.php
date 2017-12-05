<?php namespace OFFLINE\Mall\Models;

use Model;

/**
 * Model
 */
class CustomField extends Model
{
    use \October\Rain\Database\Traits\Validation;
    public $implement = ['@RainLab.Translate.Behaviors.TranslatableModel'];
    public $translatable = ['name'];
    public $jsonable = ['options'];
    public $fieldOptions = [];

    public $rules = [
        'product_id' => 'exists:offline_mall_products,id',
        'name'       => 'required',
        'type'       => 'in:text,textarea,dropdown,checkbox',
        'required'   => 'boolean',
    ];
    public $belongsTo = [
        'product' => Product::class,
    ];
    public $hasMany = [
        'options' => [CustomFieldOption::class, 'order' => 'sort_order'],
    ];
    public $belongsToMany = [
        'variants' => [
            Variant::class,
            'table'    => 'offline_mall_product_variant_custom_field_option',
            'key'      => 'custom_field_option_id',
            'otherKey' => 'variant_id',
        ],
    ];

    public $table = 'offline_mall_product_custom_fields';

    public function afterSave()
    {
        $this->setTranslatableFields();
        $this->handleFieldOptionsChanges();
    }

    public function afterFetch()
    {
        $this->setFieldOptions();
    }

    /**
     * This is a temporary fix until
     * https://github.com/rainlab/translate-plugin/issues/209
     * is resolved.
     */
    protected function setTranslatableFields()
    {
        if ( ! post('RLTranslate')) {
            return;
        }

        foreach (post('RLTranslate') as $key => $value) {
            $data = json_encode($value);

            $obj = DB::table('rainlab_translate_attributes')
                     ->where('locale', $key)
                     ->where('model_id', $this->id)
                     ->where('model_type', get_class($this->model));

            if ($obj->count() > 0) {
                $obj->update(['attribute_data' => $data]);
            }
        }
    }

    /**
     * This method creates, updates and deletes all releated field
     * options. We need to implement our own relation handler here since
     * October does not support a convenient way to manage hasManyThrough
     * relations via the backend yet.
     */
    protected function handleFieldOptionsChanges()
    {
        $this->handleRemovedFieldOptions();

        foreach ($this->fieldOptions as $index => $data) {
            $data = $this->normalizeData($data, $index);

            // Existing fields have an id
            if ($data['id']) {
                $this->updateRelatedFieldOption($data);
            } else {
                $this->createRelatedFieldOption($data);
            }
        }

        // Reload the new options into the fieldOptions attribute.
        $this->setFieldOptions();
    }

    /**
     * This method is used to populate the related field options
     * attribute with data for the repeater field in the backend.
     */
    protected function setFieldOptions()
    {
        $this->fieldOptions = $this->options()->get()->map(function ($item) {
            return [
                'id'           => $item->id,
                'option_name'  => $item->name,
                'option_price' => $item->price,
            ];
        })->toArray();
    }

    /**
     * Removes field options that are no longer present
     * in the post data sent by the repeater form widget.
     */
    protected function handleRemovedFieldOptions()
    {
        $existing = $this->options()->get()->pluck('id')->toArray();
        $sent     = collect($this->fieldOptions)->pluck('id')->toArray();

        $removed = array_diff($existing, $sent);

        CustomFieldOption::whereIn('id', $removed)->delete();
    }

    /**
     * This method updates a related field option with
     * the data sent by the repeater form widget.
     */
    protected function updateRelatedFieldOption(array $data)
    {
        $option = CustomFieldOption::find($data['id']);
        $option->fill($data);
        $option->save();

        return $option;
    }

    /**
     * This method creates a related field option with
     * the data sent by the repeater form widget.
     */
    protected function createRelatedFieldOption(array $data)
    {
        $data['id']              = null;
        $data['custom_field_id'] = $this->id;

        return CustomFieldOption::create($data);
    }

    /**
     * Here we set the sort_order attribute and rename the
     * option_* attributes. These dummy attributes are needed
     * to make the Rainlab.Translate plugin work correctly
     * in the repeater where another "name" field for the option
     * is present.
     *
     * @return array
     */
    protected function normalizeData($data, $index)
    {
        // Field is set by repeater form widget
        $sortOrder = array_flip(input('___index_fieldOptions', []));

        $data['name']       = $data['option_name'];
        $data['price']      = $data['option_price'];
        $data['sort_order'] = array_key_exists($index, $sortOrder) ? $sortOrder[$index] : 0;

        unset($data['option_name'], $data['option_price']);

        return $data;
    }
}
