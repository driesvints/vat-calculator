<?php

namespace Mpociot\VatCalculator\Facades;

use Illuminate\Support\Facades\Facade;

class VatCalculator extends Facade
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
