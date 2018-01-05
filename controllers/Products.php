<?php namespace OFFLINE\Mall\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use October\Rain\Database\Models\DeferredBinding;
use OFFLINE\Mall\Models\CustomField;
use OFFLINE\Mall\Models\CustomFieldOption;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\Property;
use OFFLINE\Mall\Models\PropertyValue;

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

    public function formAfterUpdate(Product $model)
    {
        $values     = post('PropertyValues');
        $properties = Property::whereIn('id', array_keys($values))->get();

        foreach ($values as $id => $value) {
            $pv = PropertyValue::firstOrNew([
                'describable_id'   => $model->id,
                'describable_type' => Product::class,
                'property_id'      => $id,
            ]);

            $pv->value = $value;
            $pv->save();

            // Transfer any deferred media
            $property = $properties->find($id);
            if ($property->type === 'image') {
                $media = DeferredBinding::where('master_type', PropertyValue::class)
                                        ->where('master_field', 'image')
                                        ->where('session_key', post('_session_key'))
                                        ->get();

                foreach ($media as $m) {
                    $slave                  = $m->slave_type::find($m->slave_id);
                    $slave->field           = 'image';
                    $slave->attachment_type = PropertyValue::class;
                    $slave->attachment_id   = $pv->id;
                    $slave->save();
                    $m->delete();
                }
            }
        }
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
        $this->vars['type']  = post('type');

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
        $this->vars['type']             = post('type');

        return $this->makePartial('$/offline/mall/controllers/customfields/_option_form.htm');
    }

    public function onLoadEditOptionForm()
    {
        $this->vars['optionFormWidget']    = $this->optionFormWidget;
        $this->vars['customFieldId']       = post('manage_id');
        $this->vars['customFieldOptionId'] = post('option_id');
        $this->vars['type']                = post('type');

        return $this->makePartial('$/offline/mall/controllers/customfields/_option_form.htm');
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
}
