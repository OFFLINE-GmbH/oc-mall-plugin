<?php namespace OFFLINE\Mall\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use OFFLINE\Mall\Models\CustomField;
use OFFLINE\Mall\Models\CustomFieldOption;
use OFFLINE\Mall\Models\PropertyValue;
use OFFLINE\Mall\Models\Variant;

class Products extends Controller
{
    public $implement = [
        'Backend\Behaviors\ListController',
        'Backend\Behaviors\FormController',
        'Backend.Behaviors.RelationController',
    ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $relationConfig = 'config_relation.yaml';

    public $requiredPermissions = [
        'offline.mall.manage_products',
    ];

    protected $optionFormWidget;
    protected $propertyFormWidget;

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('OFFLINE.Mall', 'mall', 'mall-products');

        $model                    = post('property_id') ? PropertyValue::find(post('property_id')) : null;
        $this->propertyFormWidget = $this->createPropertyFormWidget($model);

        $model                  = post('option_id') ? CustomFieldOption::find(post('option_id')) : null;
        $this->optionFormWidget = $this->createOptionFormWidget($model);
    }

    public function create()
    {
        $this->bodyClass = 'compact-container';
        parent::create();
    }

    public function update($recordId = null)
    {
        $this->bodyClass = 'compact-container';
        parent::update($recordId);
    }

    public function onCreateOption()
    {
        $data  = $this->optionFormWidget->getSaveData();
        $model = CustomFieldOption::findOrNew(post('edit_id'));
        $model->fill($data);
        $model->save(null, $this->optionFormWidget->getSessionKey());

        $field = $this->getCustomFieldModel();
        $field->custom_field_options()->add($model, $this->optionFormWidget->getSessionKey());

        return $this->refreshOptionsList();
    }

    public function onCreateVariant()
    {
        $data               = $this->propertyFormWidget->getSaveData();
        $model              = PropertyValue::findOrNew(post('edit_id'));
        $model->property_id = $data['property'];
        unset($data['property']);

        $model->fill($data);
        $model->save(null, $this->propertyFormWidget->getSessionKey());

        $field = $this->getVariantModel();
        $field->property_values()->add($model, $this->propertyFormWidget->getSessionKey());

        return $this->refreshPropertiesList();
    }

    public function onDeleteOption()
    {
        $recordId = post('record_id');
        $model    = CustomFieldOption::find($recordId);
        $order    = $this->getCustomFieldModel();
        $order->custom_field_options()->remove($model, $this->optionFormWidget->getSessionKey());

        return $this->refreshOptionsList();
    }

    public function onDeleteProperty()
    {
        $recordId = post('record_id');
        $model    = PropertyValue::find($recordId);
        $order    = $this->getVariantModel();
        $order->property_values()->remove($model, $this->propertyFormWidget->getSessionKey());

        return $this->refreshPropertiesList();
    }

    protected function refreshOptionsList()
    {
        $items = $this->getCustomFieldModel()
                      ->custom_field_options()
                      ->withDeferred($this->optionFormWidget->getSessionKey())
                      ->get();

        $this->vars['items'] = $items;
        $this->vars['type']  = post('type');

        return ['#optionList' => $this->makePartial('$/offline/mall/controllers/customfields/_options_list.htm')];
    }

    protected function refreshPropertiesList()
    {
        $items = $this->getVariantModel()
                      ->property_values()
                      ->withDeferred($this->propertyFormWidget->getSessionKey())
                      ->get();

        $this->vars['items'] = $items;
        $this->vars['type']  = post('type');

        return ['#optionList' => $this->makePartial('$/offline/mall/controllers/variants/_properties_list.htm')];
    }

    protected function getCustomFieldModel()
    {
        $manageId = post('manage_id');
        $order    = $manageId
            ? CustomField::find($manageId)
            : new CustomField();

        return $order;
    }

    protected function getVariantModel()
    {
        $manageId = post('manage_id');
        $order    = $manageId
            ? Variant::find($manageId)
            : new Variant();

        return $order;
    }

    public function onLoadCreateOptionForm()
    {
        $this->vars['optionFormWidget'] = $this->optionFormWidget;
        $this->vars['customFieldId']    = post('manage_id');
        $this->vars['type']             = post('type');

        return $this->makePartial('$/offline/mall/controllers/customfields/_option_form.htm');
    }

    public function onLoadCreatePropertyForm()
    {
        $this->vars['propertyFormWidget'] = $this->propertyFormWidget;
        $this->vars['manageId']           = post('manage_id');
        $this->vars['type']               = post('type');

        return $this->makePartial('$/offline/mall/controllers/variants/_property_form.htm');
    }

    public function onLoadEditOptionForm()
    {
        $this->vars['optionFormWidget']    = $this->optionFormWidget;
        $this->vars['customFieldId']       = post('manage_id');
        $this->vars['customFieldOptionId'] = post('option_id');
        $this->vars['type']                = post('type');

        return $this->makePartial('$/offline/mall/controllers/customfields/_option_form.htm');
    }

    public function onLoadEditPropertyForm()
    {
        $this->vars['propertyFormWidget'] = $this->propertyFormWidget;
        $this->vars['manageId']           = post('manage_id');
        $this->vars['editId']             = post('property_id');
        $this->vars['type']               = post('type');

        return $this->makePartial('$/offline/mall/controllers/variants/_property_form.htm');
    }

    protected function createOptionFormWidget(CustomFieldOption $model = null)
    {
        $config                    = $this->makeConfig('$/offline/mall/models/customfieldoption/fields.yaml');
        $config->alias             = 'optionForm';
        $config->arrayName         = 'Option';
        $config->model             = $model ?? new CustomFieldOption();
        $config->model->field_type = post('type');
        $widget                    = $this->makeWidget('Backend\Widgets\Form', $config);
        $widget->bindToController();

        $this->optionFormWidget = $widget;

        return $widget;
    }

    private function createPropertyFormWidget($model)
    {
        $config                    = $this->makeConfig('$/offline/mall/models/propertyvalue/fields.yaml');
        $config->alias             = 'propertyForm';
        $config->arrayName         = 'Property';
        $config->model             = $model ?? new PropertyValue();
        $config->model->field_type = post('type');
        $widget                    = $this->makeWidget('Backend\Widgets\Form', $config);
        $widget->bindToController();

        $this->propertyFormWidget = $widget;

        return $widget;
    }
}
