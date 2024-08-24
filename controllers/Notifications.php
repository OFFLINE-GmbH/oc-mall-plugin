<?php

declare(strict_types=1);

namespace OFFLINE\Mall\Controllers;

use Backend\Behaviors\FormController;
use Backend\Behaviors\ListController;
use Backend\Classes\Controller;
use BackendMenu;
use Illuminate\Support\Facades\Cache;
use October\Rain\Support\Facades\Flash;
use OFFLINE\Mall\Models\Notification;
use System\Classes\SettingsManager;

class Notifications extends Controller
{
    /**
     * Implement behaviors for this controller.
     * @var array
     */
    public $implement = [
        FormController::class,
        ListController::class,
    ];

    /**
     * The configuration file for the form controller implementation.
     * @var string
     */
    public $formConfig = 'config_form.yaml';

    /**
     * The configuration file for the list controller implementation.
     * @var string
     */
    public $listConfig = 'config_list.yaml';

    /**
     * Required admin permission to access this page.
     * @var array
     */
    public $requiredPermissions = [
        'offline.mall.manage_notifications',
    ];

    /**
     * Construct the controller.
     */
    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('October.System', 'system', 'settings');
        SettingsManager::setContext('OFFLINE.Mall', 'notification_settings');
    }

    /**
     * Toggle on Click
     * @return void
     */
    public function onToggleClicked()
    {
        $action = post('action');
        $value = $action === 'disable' ? 0 : ($action === 'enable' ? 1 : null);

        if ($value === null) {
            return;
        }

        Notification::whereIn('id', post('checked'))->update(['enabled' => $value]);
        Cache::forget(Notification::CACHE_KEY);
        Flash::success(trans('backend::lang.form.update_success', [
            'name' => trans('offline.mall::lang.common.notification'),
        ]));
    }
}
