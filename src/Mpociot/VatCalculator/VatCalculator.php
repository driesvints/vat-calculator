<?php

namespace Mpociot\VatCalculator;

use Illuminate\Contracts\Config\Repository;

class VatCalculator
{
    /**
     * @var float
     */
    protected $netPrice = 0.0;

    /**
     * @var string
     */
    protected $countryCode;

    /**
     * @var Repository
     */
    protected $config;

    /**
     * @var float
     */
    protected $taxValue = 0;

    /**
     * @var float
     */
    protected $taxRate = 0;

    /**
     * The calculate net + tax value
     * @var float
     */
    protected $value = 0;


    /**
     * @var bool
     */
    protected $company = false;

    public function __construct(Repository $config)
    {
        $this->config = $config;
    }


    /**
     * Calculate the VAT based on the net price, country code and indication if the
     * customer is a company or not.
     *
     * @param int|float $netPrice The net price to use for the calculation
     * @param null|string $countryCode The country code to use for the rate lookup
     * @param null|bool $company
     *
     * @return float
     */
    public function calculate($netPrice, $countryCode = null, $company = null)
    {
        if ($countryCode) {
            $this->setCountryCode($countryCode);
        }
        if ( !is_null($company) && $company !== $this->isCompany()) {
            $this->setCompany($company);
        }
        $this->netPrice = floatval($netPrice);
        $this->taxRate  = $this->getTaxRateForCountry($this->getCountryCode(), $this->isCompany() );
        $this->taxValue = $this->taxRate * $this->netPrice;
        $this->value    = $this->netPrice + $this->taxValue;
        return $this->value;
    }

    /**
     * @return float
     */
    public function getNetPrice()
    {
        return $this->netPrice;
    }

    /**
     * @return string
     */
    public function getCountryCode()
    {
        return strtoupper($this->countryCode);
    }

    /**
     * @param mixed $countryCode
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;
    }

    /**
     * @return float
     */
    public function getTaxRate()
    {
        return $this->taxRate;
    }

    /**
     * @return boolean
     */
    public function isCompany()
    {
        return $this->company;
    }

    /**
     * @param boolean $company
     */
    public function setCompany($company)
    {
        $this->company = $company;
    }

    /**
     * Returns the tax rate for the given country.
     *
     * @param string $countryCode
     * @param bool|false $company
     *
     * @return float
     */
    public function getTaxRateForCountry($countryCode, $company = false)
    {
        if ($company) {
            return 0;
        }
        return $this->config->get('vat_calculator.rules.' . strtoupper($countryCode), 0);
    }

    /**
     * @return float
     */
    public function getTaxValue()
    {
        return $this->taxValue;
    }
}
