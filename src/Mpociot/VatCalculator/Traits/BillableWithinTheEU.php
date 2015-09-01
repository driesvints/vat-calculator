<?php

namespace Mpociot\VatCalculator\Traits;

use Mpociot\VatCalculator\Facades\VatCalculator;

trait BillableWithinTheEU
{
    /**
     * @var int
     */
    protected $stripeTaxPercent = 0;

    /**
     * @param string $countryCode
     * @param bool|false $company
     */
    public function setTaxForCountry($countryCode, $company = false)
    {
        $this->stripeTaxPercent = ( VatCalculator::getTaxRateForCountry( $countryCode, $company ) * 100 );
    }

    /**
     * Get the tax percentage to apply to the subscription.
     *
     * @return int
     */
    public function getTaxPercent()
    {
        return $this->stripeTaxPercent;
    }
}