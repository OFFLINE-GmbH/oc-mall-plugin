<?php namespace OFFLINE\Mall\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use October\Rain\Exception\ValidationException;
use OFFLINE\Mall\Models\Currency;
use OFFLINE\Mall\Models\PriceCategory;
use System\Classes\SettingsManager;
use Backend\Behaviors\ListController;
use Backend\Behaviors\FormController;
use Backend\Behaviors\ReorderController;

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
        'offline.mall.settings.manage_price_categories',
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('October.System', 'system', 'settings');
        SettingsManager::setContext('OFFLINE.Mall', 'price_categories_settings');
    }

    public function index_onDelete()
    {
        if (in_array(PriceCategory::OLD_PRICE_CATEGORY_ID, post('checked', []))) {
            throw new ValidationException(['checked' => 'The old price category cannot be deleted.']);
        }

        parent::index_onDelete();
    }
}
