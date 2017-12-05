<?php namespace OFFLINE\Mall\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

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

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('OFFLINE.Mall', 'mall', 'mall-products');
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
}
