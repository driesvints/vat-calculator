<?php namespace Mpociot\VatCalculator\Facades;

class VatCalculator extends \Illuminate\Support\Facades\Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'vatcalculator';
    }
}
