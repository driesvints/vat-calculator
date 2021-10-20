<?php

namespace Mpociot\VatCalculator;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class VatCalculatorServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register(): void
    {
        $this->mergeConfig();
        $this->registerVatCalculator();
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/vat_calculator.php' => config_path('vat_calculator.php'),
        ]);
    }

    protected function mergeConfig(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/vat_calculator.php', 'vat_calculator');
    }

    protected function registerVatCalculator(): void
    {
        $this->app->bind(VatCalculator::class, function ($app) {
            $config = $app->make(Repository::class);

            return new VatCalculator($config);
        });

        $this->app->bind('vatcalculator', VatCalculator::class);
    }
}
