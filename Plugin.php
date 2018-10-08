<?php namespace OFFLINE\Mall;


use App;
use Backend\Facades\Backend;
use Backend\Widgets\Form;
use Cache;
use Hashids\Hashids;
use Illuminate\Support\Facades\Event;
use October\Rain\Database\Relations\Relation;
use OFFLINE\Mall\Classes\Customer\AuthManager;
use OFFLINE\Mall\Classes\Customer\DefaultSignInHandler;
use OFFLINE\Mall\Classes\Customer\DefaultSignUpHandler;
use OFFLINE\Mall\Classes\Customer\SignInHandler;
use OFFLINE\Mall\Classes\Customer\SignUpHandler;
use OFFLINE\Mall\Classes\Events\MailingEventHandler;
use OFFLINE\Mall\Classes\Index\Filebase;
use OFFLINE\Mall\Classes\Index\Index;
use OFFLINE\Mall\Classes\Payments\DefaultPaymentGateway;
use OFFLINE\Mall\Classes\Payments\Offline;
use OFFLINE\Mall\Classes\Payments\PaymentGateway;
use OFFLINE\Mall\Classes\Payments\PayPalRest;
use OFFLINE\Mall\Classes\Payments\Stripe;
use OFFLINE\Mall\Classes\Search\ProductsSearchProvider;
use OFFLINE\Mall\Classes\Utils\DefaultMoney;
use OFFLINE\Mall\Classes\Utils\Money;
use OFFLINE\Mall\Components\AddressForm;
use OFFLINE\Mall\Components\AddressList;
use OFFLINE\Mall\Components\AddressSelector;
use OFFLINE\Mall\Components\Cart;
use OFFLINE\Mall\Components\Products as ProductsComponent;
use OFFLINE\Mall\Components\CategoryFilter;
use OFFLINE\Mall\Components\Checkout;
use OFFLINE\Mall\Components\CurrencyPicker;
use OFFLINE\Mall\Components\CustomerProfile;
use OFFLINE\Mall\Components\DiscountApplier;
use OFFLINE\Mall\Components\MyAccount;
use OFFLINE\Mall\Components\OrdersList;
use OFFLINE\Mall\Components\PaymentMethodSelector;
use OFFLINE\Mall\Components\Product as ProductComponent;
use OFFLINE\Mall\Components\ShippingSelector;
use OFFLINE\Mall\Components\SignUp;
use OFFLINE\Mall\Console\ReindexProducts;
use OFFLINE\Mall\Console\SeedDemoData;
use OFFLINE\Mall\FormWidgets\Price;
use OFFLINE\Mall\FormWidgets\PropertyFields;
use OFFLINE\Mall\Models\Category;
use OFFLINE\Mall\Models\CustomerGroup;
use OFFLINE\Mall\Models\CustomField;
use OFFLINE\Mall\Models\CustomFieldOption;
use OFFLINE\Mall\Models\Discount;
use OFFLINE\Mall\Models\GeneralSettings;
use OFFLINE\Mall\Models\ImageSet;
use OFFLINE\Mall\Models\PaymentGatewaySettings;
use OFFLINE\Mall\Models\Product;
use OFFLINE\Mall\Models\PropertyValue;
use OFFLINE\Mall\Models\ShippingMethod;
use OFFLINE\Mall\Models\ShippingMethodRate;
use OFFLINE\Mall\Models\Tax;
use OFFLINE\Mall\Models\User as RainLabUser;
use OFFLINE\Mall\Models\Variant;
use OFFLINE\Mall\NotifyRules\Conditions\CustomerAttributeCondition;
use RainLab\Location\Models\Country as RainLabCountry;
use System\Classes\MarkupManager;
use System\Classes\PluginBase;
use System\Helpers\View;
use System\Twig\MailPartialTokenParser;
use Validator;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Plugin extends PluginBase
{
    public $require = ['RainLab.User', 'RainLab.Location'];

    public function boot()
    {
        $this->registerSiteSearchEvents();
        $this->registerFormWidgets();
        $this->registerStaticPagesEvents();
        $this->setContainerBindings();
        $this->addCustomValidatorRules();
        $this->extendPlugins();

        $this->registerConsoleCommand('offline.mall.seed-demo', SeedDemoData::class);
        $this->registerConsoleCommand('offline.mall.reindex', ReindexProducts::class);

        $this->setMorphMap();
        $this->registerObservers();
        $this->registerEvents();

        \Illuminate\Support\Facades\View::share('app_url', config('app.url'));
    }

    public function registerObservers()
    {
        Product::observe(\OFFLINE\Mall\Classes\Observers\ProductObserver::class);
        Variant::observe(\OFFLINE\Mall\Classes\Observers\VariantObserver::class);
        PropertyValue::observe(\OFFLINE\Mall\Classes\Observers\PropertyValueObserver::class);
    }

    public function setMorphMap()
    {
        Relation::morphMap([
            Variant::MORPH_KEY            => Variant::class,
            Product::MORPH_KEY            => Product::class,
            ImageSet::MORPH_KEY           => ImageSet::class,
            Discount::MORPH_KEY           => Discount::class,
            CustomField::MORPH_KEY        => CustomField::class,
            ShippingMethod::MORPH_KEY     => ShippingMethod::class,
            CustomFieldOption::MORPH_KEY  => CustomFieldOption::class,
            ShippingMethodRate::MORPH_KEY => ShippingMethodRate::class,
        ]);
    }

    public function registerComponents()
    {
        return [
            Cart::class                  => 'cart',
            SignUp::class                => 'signUp',
            ShippingSelector::class      => 'shippingSelector',
            AddressSelector::class       => 'addressSelector',
            AddressForm::class           => 'addressForm',
            PaymentMethodSelector::class => 'paymentMethodSelector',
            Checkout::class              => 'checkout',
            ProductsComponent::class     => 'products',
            CategoryFilter::class        => 'categoryFilter',
            ProductComponent::class      => 'product',
            DiscountApplier::class       => 'discountApplier',
            MyAccount::class             => 'myAccount',
            OrdersList::class            => 'ordersList',
            CustomerProfile::class       => 'customerProfile',
            AddressList::class           => 'addressList',
            CurrencyPicker::class        => 'currencyPicker',
        ];
    }

    public function registerFormWidgets()
    {
        return [
            PropertyFields::class => 'mall.propertyfields',
            Price::class          => 'mall.price',
        ];
    }

    public function registerMailTemplates()
    {
        return [
            'offline.mall::mail.customer.created',
            'offline.mall::mail.order.state_changed',
            'offline.mall::mail.order.shipped',
            'offline.mall::mail.checkout.succeeded',
            'offline.mall::mail.checkout.failed',
            'offline.mall::mail.payment.failed',
            'offline.mall::mail.payment.paid',
            'offline.mall::mail.payment.refunded',
        ];
    }

    public function registerMailPartials()
    {
        return [
            'mall.order.table'    => 'offline.mall::mail._partials.order.table',
            'mall.order.tracking' => 'offline.mall::mail._partials.order.tracking',
        ];
    }

    public function registerSettings()
    {
        return [
            'general_settings'          => [
                'label'       => 'offline.mall::lang.general_settings.label',
                'description' => 'offline.mall::lang.general_settings.description',
                'category'    => 'offline.mall::lang.general_settings.category',
                'icon'        => 'icon-shopping-cart',
                'class'       => GeneralSettings::class,
                'order'       => 0,
                'permissions' => ['offline.mall.settings.manage_general'],
                'keywords'    => 'shop store mall general',
            ],
            'currency_settings'         => [
                'label'       => 'offline.mall::lang.currency_settings.label',
                'description' => 'offline.mall::lang.currency_settings.description',
                'category'    => 'offline.mall::lang.general_settings.category',
                'icon'        => 'icon-money',
                'url'         => Backend::url('offline/mall/currencies'),
                'order'       => 20,
                'permissions' => ['offline.mall.settings.manage_currency'],
                'keywords'    => 'shop store mall currency',
            ],
            'payment_gateways_settings' => [
                'label'       => 'offline.mall::lang.payment_gateway_settings.label',
                'description' => 'offline.mall::lang.payment_gateway_settings.description',
                'category'    => 'offline.mall::lang.general_settings.category',
                'icon'        => 'icon-credit-card',
                'class'       => PaymentGatewaySettings::class,
                'order'       => 30,
                'permissions' => ['offline.mall.settings.manage_payment_gateways'],
                'keywords'    => 'shop store mall payment gateways',
            ],
            'notification_settings'     => [
                'label'       => 'offline.mall::lang.notification_settings.label',
                'description' => 'offline.mall::lang.notification_settings.description',
                'category'    => 'offline.mall::lang.general_settings.category',
                'icon'        => 'icon-envelope',
                'url'         => Backend::url('offline/mall/notifications'),
                'order'       => 40,
                'permissions' => ['offline.mall.manage_notifications'],
                'keywords'    => 'shop store mall notifications email mail',
            ],
        ];
    }

    public function registerMarkupTags()
    {
//        // If the mail token parser is only registered temporarily the sending
//        // of emails with the sync queue driver will fail since Twig gets initialized
//        // before the parser is registered and therefore the partial tag is unknown.
//        // @see https://github.com/octobercms/october/issues/3341#issuecomment-427586226
//        $markupManager = MarkupManager::instance();
//        $markupManager->registerTokenParsers([
//            new MailPartialTokenParser,
//        ]);

        return [
            'filters' => [
                'money' => function (...$args) {
                    return app(Money::class)->format(...$args);
                },
            ],
        ];
    }

    protected function registerStaticPagesEvents()
    {
        Event::listen('pages.menuitem.listTypes', function () {
            return [
                'mall-category'       => trans('offline.mall::lang.menu_items.single_category'),
                'all-mall-categories' => trans('offline.mall::lang.menu_items.all_categories'),
            ];
        });

        Event::listen('pages.menuitem.getTypeInfo', function ($type) {
            if ($type == 'all-mall-categories' || $type == 'mall-category') {
                return Category::getMenuTypeInfo($type);
            }
        });

        Event::listen('pages.menuitem.resolveItem', function ($type, $item, $url, $theme) {
            if ($type == 'all-mall-categories') {
                return Category::resolveCategoriesItem($item, $url, $theme);
            }
            if ($type == 'mall-category') {
                return Category::resolveCategoryItem($item, $url, $theme);
            }
        });
    }

    protected function registerSiteSearchEvents()
    {
        Event::listen('offline.sitesearch.extend', function () {
            return new ProductsSearchProvider();
        });
    }

    protected function setContainerBindings()
    {
        $this->app->bind(SignInHandler::class, function () {
            return new DefaultSignInHandler();
        });
        $this->app->bind(SignUpHandler::class, function () {
            return new DefaultSignUpHandler();
        });
        $this->app->bind(Index::class, function () {
            return new Filebase();
        });
        $this->app->singleton(Money::class, function () {
            return new DefaultMoney();
        });
        $this->app->singleton(PaymentGateway::class, function () {
            $gateway = new DefaultPaymentGateway();
            $gateway->registerProvider(new Offline());
            $gateway->registerProvider(new PayPalRest());
            $gateway->registerProvider(new Stripe());

            return $gateway;
        });
        $this->app->singleton(Hashids::class, function () {
            return new Hashids(config('app.key', 'oc-mall'), 8);
        });
        $this->app->singleton('user.auth', function () {
            return AuthManager::instance();
        });
    }

    protected function addCustomValidatorRules()
    {
        Validator::extend('non_existing_user', function ($attribute, $value, $parameters) {
            $count = RainLabUser::with('customer')
                                ->where('email', $value)
                                ->whereHas('customer', function ($q) {
                                    $q->where('is_guest', 0);
                                })->count();

            return $count === 0;
        });
    }

    protected function extendPlugins()
    {
        RainLabCountry::extend(function ($model) {
            $model->belongsToMany['taxes'] = [
                Tax::class,
                'table'    => 'offline_mall_country_tax',
                'key'      => 'country_id',
                'otherKey' => 'tax_id',
            ];
        });

        $this->extendRainLabUser();
    }

    protected function extendRainLabUser()
    {
        // Add customer_group Relation
        \RainLab\User\Models\User::extend(function ($model) {
            $model->belongsTo = [
                'customer_group' => [CustomerGroup::class, 'key' => 'offline_mall_customer_group_id'],
            ];
        });

        // Add Customer Groups menu entry to RainLab.User
        Event::listen('backend.menu.extendItems', function ($manager) {
            $manager->addSideMenuItems('RainLab.User', 'user', [
                'users' => [
                    'label'       => 'rainlab.user::lang.users.menu_label',
                    'url'         => \Backend::url('rainlab/user/users'),
                    'icon'        => 'icon-user',
                    'permissions' => ['rainlab.users.*'],
                ],
            ]);

            $manager->addSideMenuItems('RainLab.User', 'user', [
                'customer_groups' => [
                    'label'       => 'offline.mall::lang.common.customer_groups',
                    'url'         => \Backend::url('offline/mall/customergroups'),
                    'icon'        => 'icon-users',
                    'permissions' => ['rainlab.users.*', 'offline.mall.manage_customer_groups'],
                ],
            ]);
        });

        // Add Customer Groups relation to RainLab.User form
        Event::listen('backend.form.extendFields', function (Form $widget) {
            if ( ! $widget->getController() instanceof \RainLab\User\Controllers\Users) {
                return;
            }

            if ( ! $widget->model instanceof \RainLab\User\Models\User) {
                return;
            }

            $widget->addTabFields([
                'customer_group' => [
                    'label'       => trans('offline.mall::lang.common.customer_group'),
                    'type'        => 'relation',
                    'nameFrom'    => 'name',
                    'emptyOption' => trans('offline.mall::lang.common.none'),
                    'tab'         => 'rainlab.user::lang.user.account',
                ],
            ]);
        });
    }

    protected function registerEvents()
    {
        $this->app->bind('MailingEventHandler', MailingEventHandler::class);
        Event::subscribe('MailingEventHandler');
    }
}
