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
     * @var
     */
    protected $userCountryCode;

    /**
     * @var bool
     */
    protected $userIsCompany = false;

    /**
     * @param string     $countryCode
     * @param bool|false $company
     *
     * @return $this
     */
    public function setTaxForCountry($countryCode, $company = false)
    {
        $this->userCountryCode = $countryCode;
        $this->userIsCompany = $company;

        return $this;
    }

    /**
     * @param $countryCode
     *
     * @return $this
     */
    public function useTaxFrom($countryCode)
    {
        $this->userCountryCode = $countryCode;

        return $this;
    }

    /**
     * @return $this
     */
    public function asBusiness()
    {
        $this->userIsCompany = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function asIndividual()
    {
        $this->userIsCompany = false;

        return $this;
    }

    /**
     * Get the tax percentage to apply to the subscription.
     *
     * @return int
     */
    public function getTaxPercent()
    {
        return (VatCalculator::getTaxRateForCountry($this->userCountryCode, $this->userIsCompany) * 100);
    }
}
