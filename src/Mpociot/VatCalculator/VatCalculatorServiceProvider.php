<?php

namespace Mpociot\VatCalculator;

/*
 * This file is part of Teamwork
 *
 * @license MIT
 * @package Teamwork
 */

use Illuminate\Support\ServiceProvider;

class VatCalculatorServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishConfig();
        $this->registerValidatorExtension();
    }

    /**
     * Publish configuration.
     */
    protected function publishConfig()
    {
        $this->publishes([
            __DIR__.'/../../config/config.php' => config_path('vat_calculator.php'),
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfig();
        $this->registerVatCalculator();
        $this->registerFacade();
    }

    /**
     * Register the application bindings.
     *
     * @return void
     */
    protected function registerVatCalculator()
    {
        $this->app->bind('vatcalculator', \Mpociot\VatCalculator\VatCalculator::class);

        $this->app->bind(\Mpociot\VatCalculator\VatCalculator::class, function ($app) {
            $config = $app->make('Illuminate\Contracts\Config\Repository');

            return new \Mpociot\VatCalculator\VatCalculator($config);
        });
    }

    /**
     * Register the vault facade without the user having to add it to the app.php file.
     *
     * @return void
     */
    public function registerFacade()
    {
        $this->app->booting(function () {
            $loader = \Illuminate\Foundation\AliasLoader::getInstance();
            $loader->alias('VatCalculator', 'Mpociot\VatCalculator\Facades\VatCalculator');
        });
    }

    /**
     * Merges user's and teamwork's configs.
     *
     * @return void
     */
    protected function mergeConfig()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/config.php', 'vat_calculator'
        );
    }

    protected function registerValidatorExtension()
    {
        $this->loadTranslationsFrom(
            __DIR__.'/../../lang',
            'vatnumber-validator'
        );

        $this->app['validator']->extend('vat_number',
            'Mpociot\VatCalculator\Validators\VatCalculatorValidatorExtension@validateVatNumber');
    }
}
