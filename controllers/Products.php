<?php namespace OFFLINE\Mall\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use OFFLINE\Mall\Models\CustomField;
use OFFLINE\Mall\Models\CustomFieldOption;

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

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('OFFLINE.Mall', 'mall', 'mall-products');

        $this->optionFormWidget = $this->createOptionFormWidget();
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
        $model = new CustomFieldOption();
        $model->fill($data);
        $model->save();
        $field = $this->getCustomFieldModel();
        $field->custom_field_options()->add($model, $this->optionFormWidget->getSessionKey());

        return $this->refreshOptionsList();
    }

    public function onDeleteOption()
    {
        $recordId = post('record_id');
        $model    = CustomFieldOption::find($recordId);
        $order    = $this->getCustomFieldModel();
        $order->custom_field_options()->remove($model, $this->optionFormWidget->getSessionKey());

        return $this->refreshOptionsList();
    }

    protected function refreshOptionsList()
    {
        $items = $this->getCustomFieldModel()
                      ->custom_field_options()
                      ->withDeferred($this->optionFormWidget->getSessionKey())
                      ->get();

        $this->vars['items'] = $items;

        return ['#optionList' => $this->makePartial('$/offline/mall/controllers/customfields/_options_list.htm')];
    }

    protected function getCustomFieldModel()
    {
        $manageId = post('manage_id');
        $order    = $manageId
            ? CustomField::find($manageId)
            : new CustomField();

        return $order;
    }

    public function onLoadCreateOptionForm()
    {
        $this->vars['optionFormWidget'] = $this->optionFormWidget;
        $this->vars['customFieldId']    = post('manage_id');

        return $this->makePartial('$/offline/mall/controllers/customfields/_option_create_form.htm');
    }

    protected function createOptionFormWidget()
    {
        $config            = $this->makeConfig('$/offline/mall/models/customfieldoption/fields.yaml');
        $config->alias     = 'optionForm';
        $config->arrayName = 'Option';
        $config->model     = new CustomFieldOption();
        $widget            = $this->makeWidget('Backend\Widgets\Form', $config);
        $widget->bindToController();

        return $widget;
    }
}
