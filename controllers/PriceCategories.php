<?php namespace OFFLINE\Mall\Controllers;

use BackendMenu;
use Backend\Behaviors\ListController;
use Backend\Behaviors\FormController;
use Backend\Behaviors\ReorderController;
use Backend\Classes\Controller;
use October\Rain\Exception\ValidationException;
use OFFLINE\Mall\Models\PriceCategory;
use System\Classes\SettingsManager;

class PriceCategories extends Controller
{
    public $implement = [
        ListController::class,
        FormController::class,
        ReorderController::class,
    ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $reorderConfig = 'config_reorder.yaml';

    public $requiredPermissions = [
        'offline.mall.manage_price_categories',
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('October.System', 'system', 'settings');
        SettingsManager::setContext('OFFLINE.Mall', 'price_categories_settings');
    }

    public function index_onDelete()
    {
        $oldPrice = PriceCategory::enabled()->where('code', 'old_price')->first();
        if ($oldPrice && in_array($oldPrice->id, post('checked', []))) {
            throw new ValidationException(['checked' => 'The old price category cannot be deleted.']);
        }

        parent::index_onDelete();
    }
}
