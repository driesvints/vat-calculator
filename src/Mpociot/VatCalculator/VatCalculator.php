<?php

namespace Mpociot\VatCalculator;

use Illuminate\Contracts\Config\Repository;

class VatCalculator
{

    /**
     * All available tax rules
     * @var array
     */
    protected $taxRules = [
        'AT' => 0.20,
        'BE' => 0.21,
        'BG' => 0.20,
        'CY' => 0.19,
        'CZ' => 0.21,
        'DE' => 0.19,
        'DK' => 0.25,
        'EE' => 0.20,
        'EL' => 0.23,
        'ES' => 0.21,
        'FI' => 0.24,
        'FR' => 0.20,
        'GB' => 0.20,
        'IE' => 0.23,
        'IT' => 0.22,
        'HR' => 0.25,
        'HU' => 0.27,
        'LV' => 0.21,
        'LT' => 0.21,
        'LU' => 0.15,
        'MT' => 0.18,
        'NL' => 0.21,
        'NO' => 0.25,
        'PL' => 0.23,
        'PT' => 0.23,
        'RO' => 0.24,
        'SE' => 0.25,
        'SK' => 0.20,
        'SI' => 0.22,
    ];

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

    /**
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    public function __construct($app)
    {
        $this->app    = $app;
        $this->config = $this->app->make('Illuminate\Contracts\Config\Repository');
    }


    /**
     * Calculate the VAT based on the net price, country code and indication if the
     * customer is a company or not.
     *
     * @param int|float   $netPrice    The net price to use for the calculation
     * @param null|string $countryCode The country code to use for the rate lookup
     * @param null|bool   $company
     *
     * @return float
     */
    public function calculate($netPrice, $countryCode = null, $company = null)
    {
        if ($countryCode) {
            $this->setCountryCode($countryCode);
        }
        if (!is_null($company) && $company !== $this->isCompany()) {
            $this->setCompany($company);
        }
        $this->netPrice = floatval($netPrice);
        $this->taxRate  = $this->getTaxRateForCountry($this->getCountryCode(), $this->isCompany());
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
     * @param string     $countryCode
     * @param bool|false $company
     *
     * @return float
     */
    public function getTaxRateForCountry($countryCode, $company = false)
    {
        if ($company) {
            return 0;
        }
        $taxKey = 'vat_calculator.rules.' . strtoupper($countryCode);
        if ($this->config->has($taxKey)) {
            return $this->config->get($taxKey, 0);
        }
        return isset( $this->taxRules[ strtoupper($countryCode) ] ) ? $this->taxRules[ strtoupper($countryCode) ] : 0;

    }

    /**
     * @return float
     */
    public function getTaxValue()
    {
        return $this->taxValue;
    }
}
