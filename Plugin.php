<?php namespace OFFLINE\Mall;


use Event;
use October\Rain\Database\Model;
use OFFLINE\Mall\Classes\Auth\AuthManager;
use OFFLINE\Mall\Classes\Customer\DefaultSignInHandler;
use OFFLINE\Mall\Classes\Customer\DefaultSignUpHandler;
use OFFLINE\Mall\Classes\Customer\SignInHandler;
use OFFLINE\Mall\Classes\Customer\SignUpHandler;
use OFFLINE\Mall\Components\AddressInput;
use OFFLINE\Mall\Components\AddressSelector;
use OFFLINE\Mall\Components\Cart;
use OFFLINE\Mall\Components\Dependencies;
use OFFLINE\Mall\Components\ShippingSelector;
use OFFLINE\Mall\Components\SignUp;
use OFFLINE\Mall\Models\Category;
use OFFLINE\Mall\Models\CurrencySettings;
use OFFLINE\Mall\Models\Customer;
use RainLab\User\Models\User;
use Rainlab\User\Models\User as UserModel;
use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    public $require = ['RainLab.Translate', 'RainLab.User', 'OFFLINE.Cashier'];

    public function boot()
    {
        $this->registerStaticPagesEvents();
        $this->extendUserModel();

        $this->app->bind(SignInHandler::class, function () {
            return new DefaultSignInHandler();
        });
        $this->app->bind(SignUpHandler::class, function () {
            return new DefaultSignUpHandler();
        });
    }

    public function registerComponents()
    {
        return [
            Cart::class             => 'cart',
            SignUp::class           => 'signUp',
            ShippingSelector::class => 'shippingSelector',
            AddressSelector::class  => 'addressSelector',
        ];
    }

    public function registerSettings()
    {
        return [
            'currency_settings' => [
                'label'       => 'offline.mall::lang.currency_settings.label',
                'description' => 'offline.mall::lang.currency_settings.description',
                'category'    => 'offline.mall::lang.general_settings.category',
                'icon'        => 'icon-money',
                'class'       => CurrencySettings::class,
                'order'       => 20,
                'permissions' => ['offline.mall.settings.manage_currency'],
                'keywords'    => 'shop store mall currency',
            ],
        ];
    }

    public function registerMarkupTags()
    {
        return [
            'filters' => [
                'money' => 'format_money',
            ],
        ];
    }

    protected function registerStaticPagesEvents()
    {
        Event::listen('pages.menuitem.listTypes', function () {
            return [
                'all-mall-categories' => trans('offline.mall::lang.menu_items.all_categories'),
            ];
        });

        Event::listen('pages.menuitem.getTypeInfo', function ($type) {
            if ($type == 'all-mall-categories') {
                return Category::getMenuTypeInfo($type);
            }
        });

        Event::listen('pages.menuitem.resolveItem', function ($type, $item, $url, $theme) {
            if ($type == 'all-mall-categories') {
                return Category::resolveMenuItem($item, $url, $theme);
            }
        });
    }

    /**
     * Extend RainLab's User Model with the needed
     * relationships.
     */
    protected function extendUserModel()
    {
        if ( ! class_exists('RainLab\User\Models\User')) {
            return;
        }
        UserModel::extend(function (Model $model) {
            $model->hasOne['customer'] = [
                Customer::class,
            ];
        });
    }

}
