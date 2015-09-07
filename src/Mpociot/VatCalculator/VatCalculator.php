<?php

namespace Mpociot\VatCalculator;

use Illuminate\Contracts\Config\Repository;
use Mpociot\VatCalculator\Exceptions\VATCheckUnavailableException;
use SoapClient;

class VatCalculator
{
    /**
     * VAT Service check URL provided by the EU
     */
    const VAT_SERVICE_URL = "http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl";

    /**
     * We're using the free ip2c service to lookup IP 2 country
     */
    const GEOCODE_SERVICE_URL = "http://ip2c.org/";

    protected $soapClient;

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
        'LU' => 0.17,
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
        $this->app        = $app;
        $this->config     = $this->app->make('Illuminate\Contracts\Config\Repository');
        $this->soapClient = new SoapClient(self::VAT_SERVICE_URL);
    }


    /**
     * Finds the client IP address
     * @return mixed
     */
    private function getClientIP()
    {
        if (isset( $_SERVER[ 'HTTP_X_FORWARDED_FOR' ] ) && $_SERVER[ 'HTTP_X_FORWARDED_FOR' ]) {
            $clientIpAddress = $_SERVER[ 'HTTP_X_FORWARDED_FOR' ];
        } elseif( isset( $_SERVER[ 'REMOTE_ADDR' ] ) && $_SERVER[ 'REMOTE_ADDR' ] ) {
            $clientIpAddress = $_SERVER[ 'REMOTE_ADDR' ];
        } else {
            $clientIpAddress = "";
        }
        return $clientIpAddress;
    }

    /**
     * Returns the ISO 3166-1 alpha-2 two letter
     * country code for the client IP. If the
     * IP can't be resolved it returns false.
     *
     * @return bool|string
     */
    public function getIPBasedCountry()
    {
        $ip     = $this->getClientIP();
        $url    = self::GEOCODE_SERVICE_URL . $ip;
        $result = file_get_contents($url);
        switch ($result[ 0 ]) {
            case '1':
                $data = explode(';', $result);
                return $data[ 1 ];
                break;
            default:
                return false;
        }
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

    /**
     * @param $vatNumber
     *
     * @return bool
     * @throws VATCheckUnavailableException
     */
    public function isValidVATNumber($vatNumber)
    {
        $vatNumber   = str_replace([' ', '-', '.', ','], "", trim($vatNumber));
        $countryCode = substr($vatNumber, 0, 2);
        $vatNumber   = substr($vatNumber, 2);

        $client = $this->soapClient;
        if ($client) {
            try {
                $result = $client->checkVat([
                    'countryCode' => $countryCode,
                    'vatNumber'   => $vatNumber
                ]);
                return $result->valid;
            } catch (\SoapFault $e) {
                return false;
            }
        }
        throw new VATCheckUnavailableException('The VAT check service is currently unavailable. Please try again later.');

    }

    /**
     * @param SoapClient $soapClient
     */
    public function setSoapClient($soapClient)
    {
        $this->soapClient = $soapClient;
    }
}
