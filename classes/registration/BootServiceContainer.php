<?php

namespace OFFLINE\Mall\Classes\Registration;

use Barryvdh\DomPDF\Facade;
use Barryvdh\DomPDF\PDF;
use DB;
use Dompdf\Dompdf;
use Hashids\Hashids;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\Cache;
use OFFLINE\Mall\Classes\Customer\DefaultSignInHandler;
use OFFLINE\Mall\Classes\Customer\DefaultSignUpHandler;
use OFFLINE\Mall\Classes\Customer\SignInHandler;
use OFFLINE\Mall\Classes\Customer\SignUpHandler;
use OFFLINE\Mall\Classes\Index\Filebase;
use OFFLINE\Mall\Classes\Index\Index;
use OFFLINE\Mall\Classes\Index\IndexNotSupportedException;
use OFFLINE\Mall\Classes\Index\MySQL\MySQL;
use OFFLINE\Mall\Classes\Payments\DefaultPaymentGateway;
use OFFLINE\Mall\Classes\Payments\Offline;
use OFFLINE\Mall\Classes\Payments\PaymentGateway;
use OFFLINE\Mall\Classes\Payments\PayPalRest;
use OFFLINE\Mall\Classes\Payments\PostFinance;
use OFFLINE\Mall\Classes\Payments\Stripe;
use OFFLINE\Mall\Classes\User\UserProvider;
use OFFLINE\Mall\Classes\Utils\DefaultMoney;
use OFFLINE\Mall\Classes\Utils\Money;
use OFFLINE\Mall\Models\GeneralSettings;
use PDO;

trait BootServiceContainer
{
    protected function registerServices()
    {
        $this->app->bind(SignInHandler::class, fn () => new DefaultSignInHandler());
        $this->app->bind(SignUpHandler::class, fn () => new DefaultSignUpHandler());
        $this->app->singleton(Money::class, fn () => new DefaultMoney());
        $this->app->singleton(PaymentGateway::class, function () {
            $gateway = new DefaultPaymentGateway();
            $gateway->registerProvider(new Offline());
            $gateway->registerProvider(new PayPalRest());
            $gateway->registerProvider(new Stripe());
            $gateway->registerProvider(new PostFinance());

            return $gateway;
        });
        $this->app->singleton(Hashids::class, fn () => new Hashids(config('app.key', 'oc-mall'), 8));
        $this->app->bind(Index::class, function () {
            $driver = Cache::rememberForever('offline_mall.mysql.index.driver', function () {
                $driver = GeneralSettings::get('index_driver');

                if ($driver === null) {
                    GeneralSettings::set('index_driver', 'database');
                }

                return $driver;
            });

            try {
                if ($driver === 'filesystem') {
                    return new Filebase();
                } else {
                    $pdo = DB::connection()->getPdo();

                    if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == 'sqlite') {
                        $pdo->sqliteCreateFunction('JSON_CONTAINS', function ($json, $val, $path = null) {
                            $array = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
                            $val = trim($val, '"');

                            if (!empty($path) && str_starts_with($path, '$.')) {
                                $path = trim(ltrim($path, '$.'), '"');
                                $array = $array[$path] ?? [];
                            }

                            if (strpos($val, '[') == 0 && strrpos($val, ']') == strlen($val)-1) {
                                $val = json_decode($val, true)[0];
                            }

                            return in_array($val, $array, true);
                        });
                    }

                    return new MySQL();
                }
            } catch (IndexNotSupportedException $e) {
                logger()->error(
                    '[OFFLINE.Mall] Your database does not support JSON data. Your index driver has been switched to "Filesystem". Update your database to make use of database indexing.'
                );
                GeneralSettings::set('index_driver', 'filesystem');
                Cache::forget('offline_mall.mysql.index.driver');

                return new Filebase();
            }
        });

        $this->registerDomPDF();

        $this->registerUserProvider();
    }

    /**
     * Register barryvdh/laravel-dompdf
     */
    protected function registerDomPDF()
    {
        AliasLoader::getInstance()->alias('PDF', Facade::class);

        $this->app->bind('dompdf.options', function () {
            if ($defines = $this->app['config']->get('offline.mall::pdf.defines')) {
                $options = [];

                foreach ($defines as $key => $value) {
                    $key           = strtolower(str_replace('DOMPDF_', '', $key));
                    $options[$key] = $value;
                }
            } else {
                $options = $this->app['config']->get('offline.mall::pdf.options', []);
            }

            return $options;
        });

        $this->app->bind('dompdf', function () {
            $options = $this->app->make('dompdf.options');
            $dompdf  = new Dompdf($options);
            $dompdf->setBasePath(realpath(base_path('public')));

            return $dompdf;
        });
        $this->app->alias('dompdf', Dompdf::class);
        $this->app->bind('dompdf.wrapper', fn ($app) => new PDF($app['dompdf'], $app['config'], $app['files'], $app['view']));
    }

    protected function registerUserProvider()
    {
        // RainLab.User 3.0
        if (class_exists(\RainLab\User\Models\Setting::class)) {
            // RainLab.User excludes guests from logging in starting with 3.0.
            // We handle these restrictions ourselves, so we can allow guests to log in.
            $this->app->auth->provider('user', fn ($app, array $config) => new UserProvider($app['hash'], $config['model']));
        }
    }
}
