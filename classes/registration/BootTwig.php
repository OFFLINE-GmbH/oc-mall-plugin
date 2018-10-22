<?php

namespace OFFLINE\Mall\Classes\Registration;

use OFFLINE\Mall\Classes\Utils\Money;
use System\Twig\Extension as TwigExtension;
use System\Twig\Loader as TwigLoader;
use Twig_Environment;

trait BootTwig
{
    public function registerTwigEnvironment()
    {
        $this->app->singleton('mall.twig.environment', function ($app) {
            $twig = new Twig_Environment(new TwigLoader, ['auto_reload' => true]);
            $twig->addExtension(new TwigExtension);

            return $twig;
        });
    }

    public function registerMarkupTags()
    {
        $filters = [
            'money' => function (...$args) {
                return app(Money::class)->format(...$args);
            },
        ];

        // Check the translate plugin is installed
        if ( ! class_exists('RainLab\Translate\Behaviors\TranslatableModel')) {
            $filters['_']  = ['Lang', 'get'];
            $filters['__'] = ['Lang', 'choice'];
            $filters['trans_choice'] = function(...$args) {
                return trans_choice(...$args);
            };
        }

        return [
            'filters' => $filters,
        ];
    }
}
