<?php

namespace Mpociot\VatCalculator;

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
    }

    /**
     * Publish configuration.
     */
    protected function publishConfig()
    {
        $this->publishes([
            __DIR__.'/../config/vat_calculator.php' => config_path('vat_calculator.php'),
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
            __DIR__.'/../config/vat_calculator.php', 'vat_calculator'
        );
    }
}
