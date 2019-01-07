<?php

namespace Mpociot\VatCalculator;

use Illuminate\Contracts\Config\Repository;
use Mpociot\VatCalculator\Exceptions\VATCheckUnavailableException;
use SoapClient;
use SoapFault;

class VatCalculator
{
    /**
     * VAT Service check URL provided by the EU.
     */
    const VAT_SERVICE_URL = 'http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl';

    /**
     * We're using the free ip2c service to lookup IP 2 country.
     */
    const GEOCODE_SERVICE_URL = 'http://ip2c.org/';

    protected $soapClient;

    /**
     * All available tax rules and their exceptions.
     *
     * Taken from: http://ec.europa.eu/taxation_customs/resources/documents/taxation/vat/how_vat_works/rates/vat_rates_en.pdf
     *
     * @var array
     */
    protected $taxRules = [
        'AT' => [ // Austria
            'rate'       => 0.20,
            'exceptions' => [
                'Jungholz'   => 0.19,
                'Mittelberg' => 0.19,
            ],
        ],
        'BE' => [ // Belgium
            'rate' => 0.21,
        ],
        'BG' => [ // Bulgaria
            'rate' => 0.20,
        ],
        'CY' => [ // Cyprus
            'rate' => 0.19,
        ],
        'CZ' => [ // Czech Republic
            'rate' => 0.21,
        ],
        'DE' => [ // Germany
            'rate'       => 0.19,
            'exceptions' => [
                'Heligoland'            => 0,
                'Büsingen am Hochrhein' => 0,
            ],
        ],
        'DK' => [ // Denmark
            'rate' => 0.25,
        ],
        'EE' => [ // Estonia
            'rate' => 0.20,
        ],
        'EL' => [ // Hellenic Republic (Greece)
            'rate'       => 0.24,
            'exceptions' => [
                'Mount Athos' => 0,
            ],
        ],
        'ES' => [ // Spain
            'rate'       => 0.21,
            'exceptions' => [
                'Canary Islands' => 0,
                'Ceuta'          => 0,
                'Melilla'        => 0,
            ],
        ],
        'FI' => [ // Finland
            'rate' => 0.24,
        ],
        'FR' => [ // France
            'rate' => 0.20,
        ],
        'GB' => [ // United Kingdom
            'rate'       => 0.20,
            'exceptions' => [
                // UK RAF Bases in Cyprus are taxed at Cyprus rate
                'Akrotiri' => 0.19,
                'Dhekelia' => 0.19,
            ],
        ],
        'GR' => [ // Greece
            'rate'       => 0.24,
            'exceptions' => [
                'Mount Athos' => 0,
            ],
        ],
        'HR' => [ // Croatia
            'rate' => 0.25,
        ],
        'HU' => [ // Hungary
            'rate' => 0.27,
        ],
        'IE' => [ // Ireland
            'rate' => 0.23,
        ],
        'IT' => [ // Italy
            'rate'       => 0.22,
            'exceptions' => [
                'Campione d\'Italia' => 0,
                'Livigno'            => 0,
            ],
        ],
        'LT' => [ // Lithuania
            'rate' => 0.21,
        ],
        'LU' => [ // Luxembourg
            'rate' => 0.17,
        ],
        'LV' => [ // Latvia
            'rate' => 0.21,
        ],
        'MT' => [ // Malta
            'rate' => 0.18,
        ],
        'NL' => [ // Netherlands
            'rate' => 0.21,
            'rates' => [
                'high' => 0.21,
                'low' => 0.09,
            ],
        ],
        'PL' => [ // Poland
            'rate' => 0.23,
        ],
        'PT' => [ // Portugal
            'rate'       => 0.23,
            'exceptions' => [
                'Azores'  => 0.18,
                'Madeira' => 0.22,
            ],
        ],
        'RO' => [ // Romania
            'rate' => 0.19,
        ],
        'SE' => [ // Sweden
            'rate' => 0.25,
        ],
        'SI' => [ // Slovenia
            'rate' => 0.22,
        ],
        'SK' => [ // Slovakia
            'rate' => 0.20,
        ],

        // Countries associated with EU countries that have a special VAT rate
        'MC' => [ // Monaco France
            'rate' => 0.20,
        ],
        'IM' => [ // Isle of Man - United Kingdom
            'rate' => 0.20,
        ],

        // Non-EU with their own VAT requirements
        'CH' => [ // Switzerland
            'rate' => 0.077,
            'rates' => [
                'high' => 0.077,
                'low' => 0.025,
            ],
        ],
        'TR' => [ // Turkey
            'rate' => 0.18,
        ],
        'NO' => [ // Norway
            'rate' => 0.25,
        ],
    ];

    /**
     * All possible postal code exceptions.
     *
     * @var array
     */
    protected $postalCodeExceptions = [
        'AT' => [
            [
                'postalCode' => '/^6691$/',
                'code'       => 'AT',
                'name'       => 'Jungholz',
            ],
            [
                'postalCode' => '/^699[123]$/',
                'city'       => '/\bmittelberg\b/i',
                'code'       => 'AT',
                'name'       => 'Mittelberg',
            ],
        ],
        'CH' => [
            [
                'postalCode' => '/^8238$/',
                'code'       => 'DE',
                'name'       => 'Büsingen am Hochrhein',
            ],
            [
                'postalCode' => '/^6911$/',
                'code'       => 'IT',
                'name'       => "Campione d'Italia",
            ],
            // The Italian city of Domodossola has a Swiss post office also
            [
                'postalCode' => '/^3907$/',
                'code'       => 'IT',
            ],
        ],
        'DE' => [
            [
                'postalCode' => '/^87491$/',
                'code'       => 'AT',
                'name'       => 'Jungholz',
            ],
            [
                'postalCode' => '/^8756[789]$/',
                'city'       => '/\bmittelberg\b/i',
                'code'       => 'AT',
                'name'       => 'Mittelberg',
            ],
            [
                'postalCode' => '/^78266$/',
                'code'       => 'DE',
                'name'       => 'Büsingen am Hochrhein',
            ],
            [
                'postalCode' => '/^27498$/',
                'code'       => 'DE',
                'name'       => 'Heligoland',
            ],
        ],
        'ES' => [
            [
                'postalCode' => '/^(5100[1-5]|5107[0-1]|51081)$/',
                'code'       => 'ES',
                'name'       => 'Ceuta',
            ],
            [
                'postalCode' => '/^(5200[0-6]|5207[0-1]|52081)$/',
                'code'       => 'ES',
                'name'       => 'Melilla',
            ],
            [
                'postalCode' => '/^(35\d{3}|38\d{3})$/',
                'code'       => 'ES',
                'name'       => 'Canary Islands',
            ],
        ],
        'GB' => [
            // Akrotiri
            [
                'postalCode' => '/^BFPO57|BF12AT$/',
                'code'       => 'CY',
            ],
            // Dhekelia
            [
                'postalCode' => '/^BFPO58|BF12AU$/',
                'code'       => 'CY',
            ],
        ],
        'GR' => [
            [
                'postalCode' => '/^63086$/',
                'code'       => 'GR',
                'name'       => 'Mount Athos',
            ],
        ],
        'IT' => [
            [
                'postalCode' => '/^22060$/',
                'city'       => '/\bcampione\b/i',
                'code'       => 'IT',
                'name'       => "Campione d'Italia",
            ],
            [
                'postalCode' => '/^23030$/',
                'city'       => '/\blivigno\b/i',
                'code'       => 'IT',
                'name'       => 'Livigno',
            ],
        ],
        'PT' => [
            [
                'postalCode' => '/^9[0-4]\d{2,}$/',
                'code'       => 'PT',
                'name'       => 'Madeira',
            ],
            [
                'postalCode' => '/^9[5-9]\d{2,}$/',
                'code'       => 'PT',
                'name'       => 'Azores',
            ],
        ],
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
     * @var string
     */
    protected $postalCode;

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
     * The calculate net + tax value.
     *
     * @var float
     */
    protected $value = 0;

    /**
     * @var bool
     */
    protected $company = false;

    /**
     * @var string
     */
    protected $businessCountryCode;

    /**
     * @param \Illuminate\Contracts\Config\Repository
     */
    public function __construct($config = null)
    {
        $this->config = $config;

        $businessCountryKey = 'vat_calculator.business_country_code';
        if (isset($this->config) && $this->config->has($businessCountryKey)) {
            $this->setBusinessCountryCode($this->config->get($businessCountryKey, ''));
        }
    }

    /**
     * Finds the client IP address.
     *
     * @return mixed
     */
    private function getClientIP()
    {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR']) {
            $clientIpAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR']) {
            $clientIpAddress = $_SERVER['REMOTE_ADDR'];
        } else {
            $clientIpAddress = '';
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
        $ip = $this->getClientIP();
        $url = self::GEOCODE_SERVICE_URL.$ip;
        $result = file_get_contents($url);
        switch ($result[0]) {
            case '1':
                $data = explode(';', $result);

                return $data[1];
                break;
            default:
                return false;
        }
    }

    /**
     * Determines if you need to collect VAT for the given country code.
     *
     * @param $countryCode
     *
     * @return bool
     */
    public function shouldCollectVAT($countryCode)
    {
        $taxKey = 'vat_calculator.rules.'.strtoupper($countryCode);

        return isset($this->taxRules[strtoupper($countryCode)]) || (isset($this->config) && $this->config->has($taxKey));
    }

    /**
     * Calculate the VAT based on the net price, country code and indication if the
     * customer is a company or not.
     *
     * @param int|float   $netPrice    The net price to use for the calculation
     * @param null|string $countryCode The country code to use for the rate lookup
     * @param null|string $postalCode  The postal code to use for the rate exception lookup
     * @param null|bool   $company
     * @param null|string $type        The type can be low or high
     *
     * @return float
     */
    public function calculate($netPrice, $countryCode = null, $postalCode = null, $company = null, $type = null)
    {
        if ($countryCode) {
            $this->setCountryCode($countryCode);
        }
        if ($postalCode) {
            $this->setPostalCode($postalCode);
        }
        if (!is_null($company) && $company !== $this->isCompany()) {
            $this->setCompany($company);
        }
        $this->netPrice = floatval($netPrice);
        $this->taxRate = $this->getTaxRateForLocation($this->getCountryCode(), $this->getPostalCode(), $this->isCompany(), $type);
        $this->taxValue = $this->taxRate * $this->netPrice;
        $this->value = $this->netPrice + $this->taxValue;

        return $this->value;
    }

    /**
     * Calculate the net price on the gross price, country code and indication if the
     * customer is a company or not.
     *
     * @param int|float   $gross       The gross price to use for the calculation
     * @param null|string $countryCode The country code to use for the rate lookup
     * @param null|string $postalCode  The postal code to use for the rate exception lookup
     * @param null|bool   $company
     * @param null|string $type        The type can be low or high
     *
     * @return float
     */
    public function calculateNet($gross, $countryCode = null, $postalCode = null, $company = null, $type = null)
    {
        if ($countryCode) {
            $this->setCountryCode($countryCode);
        }
        if ($postalCode) {
            $this->setPostalCode($postalCode);
        }
        if (!is_null($company) && $company !== $this->isCompany()) {
            $this->setCompany($company);
        }

        $this->value = floatval($gross);
        $this->taxRate = $this->getTaxRateForLocation($this->getCountryCode(), $this->getPostalCode(), $this->isCompany(), $type);
        $this->taxValue = $this->taxRate > 0 ? $this->value / (1 + $this->taxRate) * $this->taxRate : 0;
        $this->netPrice = $this->value - $this->taxValue;

        return $this->netPrice;
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
     * @return string
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * @param mixed $postalCode
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;
    }

    /**
     * @return float
     */
    public function getTaxRate()
    {
        return $this->taxRate;
    }

    /**
     * @return bool
     */
    public function isCompany()
    {
        return $this->company;
    }

    /**
     * @param bool $company
     */
    public function setCompany($company)
    {
        $this->company = $company;
    }

    /**
     * @param string $businessCountryCode
     */
    public function setBusinessCountryCode($businessCountryCode)
    {
        $this->businessCountryCode = $businessCountryCode;
    }

    /**
     * Returns the tax rate for the given country code.
     * This method is used to allow backwards compatibility.
     *
     * @param $countryCode
     * @param bool $company
     * @param string $type
     *
     * @return float
     */
    public function getTaxRateForCountry($countryCode, $company = false, $type = null)
    {
        return $this->getTaxRateForLocation($countryCode, null, $company, $type);
    }

    /**
     * Returns the tax rate for the given country code.
     * If a postal code is provided, it will try to lookup the different
     * postal code exceptions that are possible.
     *
     * @param string      $countryCode
     * @param string|null $postalCode
     * @param bool|false  $company
     * @param string|null $type
     *
     * @return float
     */
    public function getTaxRateForLocation($countryCode, $postalCode = null, $company = false, $type = null)
    {
        if ($company && strtoupper($countryCode) !== strtoupper($this->businessCountryCode)) {
            return 0;
        }
        $taxKey = 'vat_calculator.rules.'.strtoupper($countryCode);
        if (isset($this->config) && $this->config->has($taxKey)) {
            return $this->config->get($taxKey, 0);
        }

        if (isset($this->postalCodeExceptions[$countryCode]) && $postalCode !== null) {
            foreach ($this->postalCodeExceptions[$countryCode] as $postalCodeException) {
                if (!preg_match($postalCodeException['postalCode'], $postalCode)) {
                    continue;
                }
                if (isset($postalCodeException['name'])) {
                    return $this->taxRules[$postalCodeException['code']]['exceptions'][$postalCodeException['name']];
                }

                return $this->taxRules[$postalCodeException['code']]['rate'];
            }
        }

        if ($type !== null) {
            return isset($this->taxRules[strtoupper($countryCode)]['rates'][$type]) ? $this->taxRules[strtoupper($countryCode)]['rates'][$type] : 0;
        }

        return isset($this->taxRules[strtoupper($countryCode)]['rate']) ? $this->taxRules[strtoupper($countryCode)]['rate'] : 0;
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
     * @throws VATCheckUnavailableException
     *
     * @return bool
     */
    public function isValidVATNumber($vatNumber)
    {
        $details = self::getVATDetails($vatNumber);

        if ($details) {
            return $details->valid;
        } else {
            return false;
        }
    }

    /**
     * @param $vatNumber
     *
     * @throws VATCheckUnavailableException
     *
     * @return object|false
     */
    public function getVATDetails($vatNumber)
    {
        $vatNumber = str_replace([' ', '-', '.', ','], '', trim($vatNumber));
        $countryCode = substr($vatNumber, 0, 2);
        $vatNumber = substr($vatNumber, 2);
        $this->initSoapClient();
        $client = $this->soapClient;
        if ($client) {
            try {
                $result = $client->checkVat([
                    'countryCode' => $countryCode,
                    'vatNumber' => $vatNumber,
                ]);
                return $result;
            } catch (SoapFault $e) {
                if (isset($this->config) && $this->config->get('vat_calculator.forward_soap_faults')) {
                    throw new VATCheckUnavailableException($e->getMessage(), $e->getCode(), $e->getPrevious());
                }

                return false;
            }
        }
        throw new VATCheckUnavailableException('The VAT check service is currently unavailable. Please try again later.');
    }

    /**
     * @throws VATCheckUnavailableException
     *
     * @return void
     */
    public function initSoapClient()
    {
        if (is_object($this->soapClient) || $this->soapClient === false) {
            return;
        }
        try {
            $this->soapClient = new SoapClient(self::VAT_SERVICE_URL);
        } catch (SoapFault $e) {
            if (isset($this->config) && $this->config->get('vat_calculator.forward_soap_faults')) {
                throw new VATCheckUnavailableException($e->getMessage(), $e->getCode(), $e->getPrevious());
            }

            $this->soapClient = false;
        }
    }

    /**
     * @param SoapClient $soapClient
     */
    public function setSoapClient($soapClient)
    {
        $this->soapClient = $soapClient;
    }
}
