<?php

namespace OFFLINE\Mall\Classes\Registration;

use App;
use Backend\Widgets\Form;
use Illuminate\Support\Facades\Event;
use OFFLINE\Mall\Models\Address;
use OFFLINE\Mall\Models\Customer;
use OFFLINE\Mall\Models\CustomerGroup;
use OFFLINE\Mall\Models\Tax;
use OFFLINE\Mall\Models\User as MallUser;
use RainLab\Location\Models\Country as RainLabCountry;
use RainLab\User\Controllers\Users as RainLabUsersController;
use RainLab\User\Models\User as RainLabUser;
use System\Classes\PluginManager;

trait BootExtensions
{
    protected function registerExtensions()
    {
        if (PluginManager::instance()->exists('RainLab.Location')) {
            $this->extendRainLabCountry();
        }
        if (PluginManager::instance()->exists('RainLab.User')) {
            $this->extendRainLabUser();
        }
    }

    protected function extendRainLabCountry()
    {
        RainLabCountry::extend(function ($model) {
            $model->belongsToMany['taxes'] = [
                Tax::class,
                'table'    => 'offline_mall_country_tax',
                'key'      => 'country_id',
                'otherKey' => 'tax_id',
            ];
        });
    }

    protected function extendRainLabUser()
    {
        // Use custom user model
        App::singleton('user.auth', function () {
            return \OFFLINE\Mall\Classes\Customer\AuthManager::instance();
        });

        RainLabUser::extend(function ($model) {
            $model->hasOne['customer']          = Customer::class;
            $model->belongsTo['customer_group'] = [CustomerGroup::class, 'key' => 'offline_mall_customer_group_id'];
            $model->hasManyThrough['addresses']        = [
                Address::class,
                'key'        => 'user_id',
                'through'    => Customer::class,
                'throughKey' => 'id',
            ];
            $model->rules['surname']            = 'required';
            $model->rules['name']               = 'required';
        });

        RainLabUsersController::extend(function (RainLabUsersController $users) {
            if (!isset($users->relationConfig)) {
                $users->addDynamicProperty('relationConfig');
            }
            $myConfigPath = '$/offline/mall/controllers/users/config_relation.yaml';
            $users->relationConfig = $users->mergeConfig(
                $users->relationConfig,
                $myConfigPath
            );
            // Extend the Users controller with the Relation behaviour that is needed
            // to display the addresses relation widget above.
            if (!$users->isClassExtendedWith('Backend.Behaviors.RelationController')) {
                $users->extendClassWith(\Backend\Behaviors\RelationController::class);
            }
        });

        MallUser::extend(function ($model) {
            $model->rules['surname'] = 'required';
            $model->rules['name']    = 'required';
        });

        // Add Customer Groups menu entry to RainLab.User
        Event::listen('backend.menu.extendItems', function ($manager) {
            $manager->addSideMenuItems('RainLab.User', 'user', [
                'customer_groups' => [
                    'label'       => 'offline.mall::lang.common.customer_groups',
                    'url'         => \Backend::url('offline/mall/customergroups'),
                    'icon'        => 'icon-users',
                    'permissions' => ['offline.mall.manage_customer_groups'],
                ],
            ]);
            $manager->addSideMenuItems('RainLab.User', 'user', [
                'customer_addresses' => [
                    'label'       => 'offline.mall::lang.common.addresses',
                    'url'         => \Backend::url('offline/mall/addresses'),
                    'icon'        => 'icon-home',
                    'permissions' => ['offline.mall.manage_customer_addresses'],
                ],
            ]);
        }, 5);

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
                    'tab'         => 'offline.mall::lang.plugin.name',
                ],
                //
                // This feature is blocked by https://github.com/octobercms/october/issues/2508
                //
                // 'addresses'      => [
                //     'label' => trans('offline.mall::lang.common.addresses'),
                //     'type'  => 'partial',
                //     'path'  => '$/offline/mall/controllers/users/_addresses.htm',
                //     'tab'   => 'offline.mall::lang.plugin.name',
                // ],
            ]);
        }, 5);
    }
}
