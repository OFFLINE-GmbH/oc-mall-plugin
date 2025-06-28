<?php

namespace OFFLINE\Mall\Classes\Registration;

use Backend;
use Backend\Widgets\Filter;
use Backend\Widgets\Form;
use Backend\Widgets\Lists;
use Event;
use October\Rain\Database\Builder;
use OFFLINE\Mall\Models\Address;
use OFFLINE\Mall\Models\Customer;
use OFFLINE\Mall\Models\CustomerGroup;
use OFFLINE\Mall\Models\Tax;
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
        RainLabUser::extend(function (RainLabUser $model) {
            $model->hasOne['customer']          = Customer::class;
            $model->belongsTo['customer_group'] = [CustomerGroup::class, 'key' => 'offline_mall_customer_group_id'];
            $model->hasManyThrough['addresses']        = [
                Address::class,
                'key'        => 'user_id',
                'through'    => Customer::class,
                'throughKey' => 'id',
            ];
            $model->addFillable([
                'customer_group',
                'offline_mall_customer_group_id',
            ]);

            // RainLab.User 3.0
            if (class_exists(\RainLab\User\Models\Setting::class)) {
                $model->rules['first_name'] = 'required';
                $model->rules['last_name']  = 'required';
            } else {
                $model->rules['surname'] = 'required';
                $model->rules['name']    = 'required';
            }

            $model->addDynamicMethod('scopeCustomer', function (Builder $builder) {
                $builder->whereHas('customer');

                return $builder;
            });

            $model->addDynamicMethod('scopeHasCustomerFilter', function (Builder $builder, $scopes) {
                if ($scopes->value == '1') {
                    $builder->doesntHave('customer');
                } elseif ($scopes->value == '2') {
                    $builder->whereHas('customer');
                }

                return $builder;
            });

            // Create a customer for a User model that does not have a customer attached.
            $model->addDynamicMethod('attachCustomer', function () use ($model) {
                if ($model->customer || $model->is_guest) {
                    return;
                }

                $customer = new Customer();
                $customer->firstname = $model->name;
                $customer->lastname = $model->surname;
                $customer->user_id = $model->id;
                $customer->is_guest = false;
                $customer->save();

                $model->customer = $customer;
            });
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
            // RainLab.User 3.0 does not need this.
            if (!class_exists(\RainLab\User\Models\Setting::class)) {
                if (!$users->isClassExtendedWith(Backend\Behaviors\RelationController::class)) {
                    $users->extendClassWith(Backend\Behaviors\RelationController::class);
                }
            }
        });

        // Add Customer Groups menu entry to RainLab.User
        Event::listen('backend.menu.extendItems', function ($manager) {
            $manager->addSideMenuItems('RainLab.User', 'user', [
                'customer_groups' => [
                    'label'       => 'offline.mall::lang.common.customer_groups',
                    'url'         => Backend::url('offline/mall/customergroups'),
                    'icon'        => 'icon-users',
                    'permissions' => ['offline.mall.manage_customer_groups'],
                ],
            ]);
            $manager->addSideMenuItems('RainLab.User', 'user', [
                'customer_addresses' => [
                    'label'       => 'offline.mall::lang.common.addresses',
                    'url'         => Backend::url('offline/mall/addresses'),
                    'icon'        => 'icon-home',
                    'permissions' => ['offline.mall.manage_customer_addresses'],
                ],
            ]);
        }, 5);

        // Add Customer Groups relation to RainLab.User form
        Event::listen('backend.form.extendFields', function (Form $widget) {
            if (! $widget->getController() instanceof RainLabUsersController) {
                return;
            }

            if (! $widget->model instanceof RainLabUser) {
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
                //'addresses'      => [
                //    'label' => trans('offline.mall::lang.common.addresses'),
                //    'type'  => 'partial',
                //    'path'  => '$/offline/mall/controllers/users/_addresses.htm',
                //    'tab'   => 'offline.mall::lang.plugin.name',
                //],
            ]);
        }, 5);

        // Add Customer Group on RainLab.User List
        Event::listen('backend.list.extendColumns', function (Lists $list) {
            if (!$list->getController() instanceof RainLabUsersController) {
                return;
            }

            if (!$list->getModel() instanceof RainLabUser) {
                return;
            }

            // Add a new column
            $list->addColumns([
                'customer_group' => [
                    'label'     => trans('offline.mall::lang.common.customer_group'),
                    'default'   => '',
                    'after'     => 'email',
                    'relation'  => 'customer_group',
                    'select'    => 'name',
                    'sortable'  => true,
                ],
            ]);
        });

        // Add Customer Group on RainLab.User List
        Event::listen('backend.filter.extendScopes', function (Filter $filter) {
            if (!$filter->getController() instanceof RainLabUsersController) {
                return;
            }

            if (!$filter->getModel() instanceof RainLabUser) {
                return;
            }

            $filter->addScopes([
                'has_customer' => [
                    'label'         => trans('offline.mall::lang.order.customer'),
                    'type'          => 'switch',
                    'conditions'    => [
                        'offline_mall_customers.id = null',
                        'offline_mall_customers.id <> null',
                    ],
                    'modelScope'    => 'hasCustomerFilter',
                ],
            ]);
        });
    }
}
