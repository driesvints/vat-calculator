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
    const VAT_SERVICE_URL = 'https://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl';

    /**
     * @var SoapClient
     */
    protected $soapClient;

    /**
     * All available tax rules and their exceptions.
     *
     * Taken from: https://taxfoundation.org/value-added-tax-2021-vat-rates-in-europe/
     *
     * @var array
     */
    protected $taxRules = [
        'AT' => [ // Austria
            'rate' => 0.20,
            'exceptions' => [
                'Jungholz' => 0.19,
                'Mittelberg' => 0.19,
            ],
            'rates' => [
                'high' => 0.20,
                'low' => 0.10,
                'low1' => 0.13,
                'low2' => 0.05,
                'parking' => 0.13,
            ],
        ],
        'BE' => [ // Belgium
            'rate' => 0.21,
            'rates' => [
                'high' => 0.21,
                'low' => 0.09,
                'low1' => 0.12,
                'parking' => 0.0012,
            ],
        ],
        'BG' => [ // Bulgaria
            'rate' => 0.20,
            'rates' => [
                'high' => 0.20,
                'low' => 0.09,
            ],
        ],
        'CY' => [ // Cyprus
            'rate' => 0.19,
            'rates' => [
                'high' => 0.19,
                'low' => 0.05,
                'low1' => 0.09,
            ],
        ],
        'CZ' => [ // Czech Republic
            'rate' => 0.21,
            'rates' => [
                'high' => 0.21,
                'low' => 0.10,
                'low1' => 0.15,
            ],
        ],
        'DE' => [ // Germany
            'rate' => 0.19,
            'exceptions' => [
                'Heligoland' => 0,
                'Büsingen am Hochrhein' => 0,
            ],
            'rates' => [
                'high' => 0.19,
                'low' => 0.07,
            ],
        ],
        'DK' => [ // Denmark
            'rate' => 0.25,
            'rates' => [
                'high' => 0.25,
            ],
        ],
        'EE' => [ // Estonia
            'rate' => 0.20,
            'rates' => [
                'high' => 0.20,
                'low' => 0.09,
            ],
        ],
        'EL' => [ // Hellenic Republic (Greece)
            'rate' => 0.24,
            'exceptions' => [
                'Mount Athos' => 0,
            ],
            'rates' => [
                'high' => 0.24,
                'low' => 0.06,
                'low1' => 0.13,
            ],
        ],
        'ES' => [ // Spain
            'rate' => 0.21,
            'exceptions' => [
                'Canary Islands' => 0,
                'Ceuta' => 0,
                'Melilla' => 0,
            ],
            'rates' => [
                'high' => 0.21,
                'low' => 0.10,
                'super-reduced' => 0.04,
            ],
        ],
        'FI' => [ // Finland
            'rate' => 0.24,
            'rates' => [
                'high' => 0.24,
                'low' => 0.10,
                'low1' => 0.14,
            ],
        ],
        'FR' => [ // France
            'rate' => 0.20,
            'exceptions' => [
                // Overseas France
                'Reunion' => 0.085,
                'Martinique' => 0.085,
                'Guadeloupe' => 0.085,
                'Guyane' => 0,
                'Mayotte' => 0,
            ],
            'rates' => [
                'high' => 0.20,
                'low' => 0.055,
                'low1' => 0.10,
                'super-reduced' => 0.021,
            ],
        ],
        'GR' => [ // Greece
            'rate' => 0.24,
            'exceptions' => [
                'Mount Athos' => 0,
            ],
            'rates' => [
                'high' => 0.24,
                'low' => 0.06,
                'low1' => 0.13,
            ],
        ],
        'HR' => [ // Croatia
            'rate' => 0.25,
            'rates' => [
                'high' => 0.25,
                'low' => 0.05,
                'low1' => 0.13,
            ],
        ],
        'HU' => [ // Hungary
            'rate' => 0.27,
            'rates' => [
                'high' => 0.27,
                'low' => 0.05,
                'low1' => 0.18,
            ],
        ],
        'IE' => [ // Ireland
            'rate' => 0.23,
            'rates' => [
                'high' => 0.23,
                'low' => 0.09,
                'low1' => 0.135,
                'super-reduced' => 0.048,
                'parking' => 0.135,
            ],
        ],
        'IT' => [ // Italy
            'rate' => 0.22,
            'exceptions' => [
                'Campione d\'Italia' => 0,
                'Livigno' => 0,
            ],
            'rates' => [
                'high' => 0.22,
                'low' => 0.05,
                'low1' => 0.10,
                'super-reduced' => 0.04,
            ],
        ],
        'LT' => [ // Lithuania
            'rate' => 0.21,
            'rates' => [
                'high' => 0.21,
                'low' => 0.05,
                'low1' => 0.09,
            ],
        ],
        'LU' => [ // Luxembourg
            'rate' => 0.16,
            'rates' => [
                'high' => 0.16,
                'low' => 0.07,
                'super-reduced' => 0.03,
                'parking' => 0.13,
            ],
        ],
        'LV' => [ // Latvia
            'rate' => 0.21,
            'rates' => [
                'high' => 0.21,
                'low' => 0.05,
                'low1' => 0.12,
            ],
        ],
        'MT' => [ // Malta
            'rate' => 0.18,
            'rates' => [
                'high' => 0.18,
                'low' => 0.05,
                'low1' => 0.07,
            ],
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
            'rates' => [
                'high' => 0.23,
                'low' => 0.06,
                'low1' => 0.08,
            ],
        ],
        'PT' => [ // Portugal
            'rate' => 0.23,
            'exceptions' => [
                'Azores' => 0.18,
                'Madeira' => 0.22,
            ],
            'rates' => [
                'high' => 0.23,
                'low' => 0.06,
                'low1' => 0.13,
                'parking' => 0.13,
            ],
        ],
        'RO' => [ // Romania
            'rate' => 0.19,
            'rates' => [
                'high' => 0.19,
                'low' => 0.05,
                'low1' => 0.09,
            ],
        ],
        'SE' => [ // Sweden
            'rate' => 0.25,
            'rates' => [
                'high' => 0.25,
                'low' => 0.06,
                'low1' => 0.12,
            ],
        ],
        'SI' => [ // Slovenia
            'rate' => 0.22,
            'rates' => [
                'high' => 0.22,
                'low' => 0.05,
                'low1' => 0.095,
            ],
        ],
        'SK' => [ // Slovakia
            'rate' => 0.20,
            'rates' => [
                'high' => 0.20,
                'low' => 0.10,
            ],
        ],

        // Countries associated with EU countries that have a special VAT rate -- https://www.easytax.co/en/countries/monaco/
        'MC' => [ // Monaco France
            'rate' => 0.20,
            'rates' => [
                'high' => 0.20,
                'low' => 0.10,
                'low1' => 0.055,
            ],
        ],
        'IM' => [ // Isle of Man - United Kingdom -- https://www.gov.im/categories/tax-vat-and-your-money/customs-and-excise/technical-information-vat-duty-and-interest-rates/vat-rates/
            'rate' => 0.20,
            'rates' => [
                'high' => 0.20,
                'low' => 0.05,
            ],
        ],

        // Non-EU with their own VAT requirements -- https://www.estv.admin.ch/estv/en/home/value-added-tax/vat-rates-switzerland.html
        'CH' => [ // Switzerland -- INFO: ON 01.01.2024 VAT RATES CHANGE IN CH
            'rate' => 0.077,
            'rates' => [
                'high' => 0.077,
                'low' => 0.025,
                'super-reduced' => 0.037,
            ],
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
                'code' => 'AT',
                'name' => 'Jungholz',
            ],
            [
                'postalCode' => '/^699[123]$/',
                'city' => '/\bmittelberg\b/i',
                'code' => 'AT',
                'name' => 'Mittelberg',
            ],
        ],
        'CH' => [
            [
                'postalCode' => '/^8238$/',
                'code' => 'DE',
                'name' => 'Büsingen am Hochrhein',
            ],
            [
                'postalCode' => '/^6911$/',
                'code' => 'IT',
                'name' => "Campione d'Italia",
            ],
            // The Italian city of Domodossola has a Swiss post office also
            [
                'postalCode' => '/^3907$/',
                'code' => 'IT',
            ],
        ],
        'DE' => [
            [
                'postalCode' => '/^87491$/',
                'code' => 'AT',
                'name' => 'Jungholz',
            ],
            [
                'postalCode' => '/^8756[789]$/',
                'city' => '/\bmittelberg\b/i',
                'code' => 'AT',
                'name' => 'Mittelberg',
            ],
            [
                'postalCode' => '/^78266$/',
                'code' => 'DE',
                'name' => 'Büsingen am Hochrhein',
            ],
            [
                'postalCode' => '/^27498$/',
                'code' => 'DE',
                'name' => 'Heligoland',
            ],
        ],
        'ES' => [
            [
                'postalCode' => '/^(5100[1-5]|5107[0-1]|51081)$/',
                'code' => 'ES',
                'name' => 'Ceuta',
            ],
            [
                'postalCode' => '/^(5200[0-6]|5207[0-1]|52081)$/',
                'code' => 'ES',
                'name' => 'Melilla',
            ],
            [
                'postalCode' => '/^(35\d{3}|38\d{3})$/',
                'code' => 'ES',
                'name' => 'Canary Islands',
            ],
        ],
        'FR' => [
            [
                'postalCode' => '/^971\d{2,}$/',
                'code' => 'FR',
                'name' => 'Guadeloupe',
            ],
            [
                'postalCode' => '/^972\d{2,}$/',
                'code' => 'FR',
                'name' => 'Martinique',
            ],
            [
                'postalCode' => '/^973\d{2,}$/',
                'code' => 'FR',
                'name' => 'Guyane',
            ],
            [
                'postalCode' => '/^974\d{2,}$/',
                'code' => 'FR',
                'name' => 'Reunion',
            ],
            [
                'postalCode' => '/^976\d{2,}$/',
                'code' => 'FR',
                'name' => 'Mayotte',
            ],
        ],
        'GB' => [
            // Akrotiri
            [
                'postalCode' => '/^BFPO57|BF12AT$/',
                'code' => 'CY',
            ],
            // Dhekelia
            [
                'postalCode' => '/^BFPO58|BF12AU$/',
                'code' => 'CY',
            ],
        ],
        'GR' => [
            [
                'postalCode' => '/^63086$/',
                'code' => 'GR',
                'name' => 'Mount Athos',
            ],
        ],
        'IT' => [
            [
                'postalCode' => '/^22061$/',
                'city' => '/\bcampione\b/i',
                'code' => 'IT',
                'name' => "Campione d'Italia",
            ],
            [
                'postalCode' => '/^23041$/',
                'city' => '/\blivigno\b/i',
                'code' => 'IT',
                'name' => 'Livigno',
            ],
        ],
        'PT' => [
            [
                'postalCode' => '/^9[0-4]\d{2,}$/',
                'code' => 'PT',
                'name' => 'Madeira',
            ],
            [
                'postalCode' => '/^9[5-9]\d{2,}$/',
                'code' => 'PT',
                'name' => 'Azores',
            ],
        ],
    ];

    /**
     * Regular expression patterns per country code for VAT.
     *
     * @var array
     *
     * @link https://ec.europa.eu/taxation_customs/vies/faq.html?locale=en#item_11
     */
    protected $patterns = [
        'AT' => 'U[A-Z\d]{8}',
        'BE' => '(0\d{9}|\d{10})',
        'BG' => '\d{9,10}',
        'CY' => '\d{8}[A-Z]',
        'CZ' => '\d{8,10}',
        'DE' => '\d{9}',
        'DK' => '(\d{2} ?){3}\d{2}',
        'EE' => '\d{9}',
        'EL' => '\d{9}',
        'ES' => '([A-Z]\d{7}[A-Z]|\d{8}[A-Z]|[A-Z]\d{8})',
        'FI' => '\d{8}',
        'FR' => '[A-Z\d]{2}\d{9}',
        'GB' => '(\d{9}|\d{12}|(GD|HA)\d{3})',
        'HR' => '\d{11}',
        'HU' => '\d{8}',
        'IE' => '([A-Z\d]{8}|[A-Z\d]{9})',
        'IT' => '\d{11}',
        'LT' => '(\d{9}|\d{12})',
        'LU' => '\d{8}',
        'LV' => '\d{11}',
        'MT' => '\d{8}',
        'NL' => '\d{9}B\d{2}',
        'PL' => '\d{10}',
        'PT' => '\d{9}',
        'RO' => '\d{2,10}',
        'SE' => '\d{12}',
        'SI' => '\d{8}',
        'SK' => '\d{10}',
    ];

    /**
     * @var float
     */
    protected $netPrice = 0.0;

    /**
     * @var string
     */
    protected $countryCode = '';

    /**
     * @var string
     */
    protected $postalCode = '';

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
    protected $businessCountryCode = '';

    /**
     * @var string
     */
    protected $ukValidationEndpoint = 'https://api.service.hmrc.gov.uk';

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
     * Determines if you need to collect VAT for the given country code.
     *
     * @param  string  $countryCode
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
     * @param  int|float  $netPrice  The net price to use for the calculation
     * @param  null|string  $countryCode  The country code to use for the rate lookup
     * @param  null|string  $postalCode  The postal code to use for the rate exception lookup
     * @param  null|bool  $company
     * @param  null|string  $type  The type can be low or high
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

        if ($company && $company !== $this->isCompany()) {
            $this->setCompany($company);
        }

        $this->netPrice = floatval($netPrice);
        $this->taxRate = $this->getTaxRateForLocation($this->getCountryCode(), $this->getPostalCode(), $this->isCompany(), $type);
        $this->taxValue = round($this->taxRate * $this->netPrice, 2);
        $this->value = round($this->netPrice + $this->taxValue, 2);

        return $this->value;
    }

    /**
     * Calculate the net price on the gross price, country code and indication if the
     * customer is a company or not.
     *
     * @param  int|float  $gross  The gross price to use for the calculation
     * @param  null|string  $countryCode  The country code to use for the rate lookup
     * @param  null|string  $postalCode  The postal code to use for the rate exception lookup
     * @param  null|bool  $company
     * @param  null|string  $type  The type can be low or high
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

        if ($company && $company !== $this->isCompany()) {
            $this->setCompany($company);
        }

        $this->value = floatval($gross);
        $this->taxRate = $this->getTaxRateForLocation($this->getCountryCode(), $this->getPostalCode(), $this->isCompany(), $type);
        $this->taxValue = round($this->taxRate > 0 ? $this->value / (1 + $this->taxRate) * $this->taxRate : 0, 2);
        $this->netPrice = round($this->value - $this->taxValue, 2);

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
     * @param  mixed  $countryCode
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode ?? '';
    }

    /**
     * @return string
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * @param  mixed  $postalCode
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode ?? '';
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
     * @param  bool  $company
     */
    public function setCompany($company)
    {
        $this->company = $company;
    }

    /**
     * @param  string  $businessCountryCode
     */
    public function setBusinessCountryCode($businessCountryCode)
    {
        $this->businessCountryCode = $businessCountryCode;
    }

    /**
     * Returns the tax rate for the given country code.
     * This method is used to allow backwards compatibility.
     *
     * @param  string  $countryCode
     * @param  bool  $company
     * @param  string|null  $type
     * @return float
     */
    public function getTaxRateForCountry($countryCode, $company = false, $type = null)
    {
        return $this->getTaxRateForLocation($countryCode, '', $company, $type);
    }

    /**
     * Returns the tax rate for the given country code.
     * If a postal code is provided, it will try to lookup the different
     * postal code exceptions that are possible.
     *
     * @param  string  $countryCode
     * @param  string|null  $postalCode
     * @param  bool  $company
     * @param  string|null  $type
     * @return float
     */
    public function getTaxRateForLocation($countryCode, $postalCode = null, $company = false, $type = null)
    {
        if ($company && strtoupper($countryCode) !== strtoupper($this->businessCountryCode)) {
            return 0;
        }

        if ($type) {
            $taxKey = 'vat_calculator.rules.'.strtoupper($countryCode).'.rates.'.$type;
        } else {
            $taxKey = 'vat_calculator.rules.'.strtoupper($countryCode).'.rate';
        }

        if (isset($this->config) && $this->config->has($taxKey)) {
            return $this->config->get($taxKey, 0);
        }

        if (isset($this->postalCodeExceptions[$countryCode]) && $postalCode) {
            foreach ($this->postalCodeExceptions[$countryCode] as $postalCodeException) {
                if (! preg_match($postalCodeException['postalCode'], $postalCode)) {
                    continue;
                }

                if (isset($postalCodeException['name'])) {
                    return $this->taxRules[$postalCodeException['code']]['exceptions'][$postalCodeException['name']];
                }

                return $this->taxRules[$postalCodeException['code']]['rate'];
            }
        }

        if ($type) {
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
     * Validate a VAT number format without checking if the VAT number was really issued.
     *
     * @param  string  $vatNumber
     * @return bool
     */
    public function isValidVatNumberFormat($vatNumber)
    {
        $vatNumber = str_replace([' ', "\xC2\xA0", "\xA0", '-', '.', ','], '', trim($vatNumber));

        if ($vatNumber === '') {
            return false;
        }

        $countryCode = substr($vatNumber, 0, 2);
        $vatNumber = substr($vatNumber, 2);

        if (! isset($this->patterns[$countryCode])) {
            return false;
        }

        return preg_match('/^'.$this->patterns[$countryCode].'$/', $vatNumber) > 0;
    }

    /**
     * @param  string  $vatNumber
     * @return bool
     *
     * @throws VATCheckUnavailableException
     */
    public function isValidVATNumber($vatNumber)
    {
        $details = $this->getVATDetails($vatNumber);

        if ($details) {
            return is_array($details) ? isset($details['vatNumber']) : $details->valid;
        }

        return false;
    }

    /**
     * @param  string  $vatNumber
     * @return object|false
     *
     * @throws VATCheckUnavailableException
     */
    public function getVATDetails($vatNumber)
    {
        $vatNumber = str_replace([' ', "\xC2\xA0", "\xA0", '-', '.', ','], '', trim($vatNumber));
        $countryCode = substr($vatNumber, 0, 2);
        $vatNumber = substr($vatNumber, 2);

        if (strtoupper($countryCode) === 'GB') {
            $apiHeaders = get_headers("$this->ukValidationEndpoint/organisations/vat/check-vat-number/lookup/$vatNumber");
            $apiHeaders = explode(' ', $apiHeaders[0]);
            $apiStatusCode = (int) $apiHeaders[1];

            if ($apiStatusCode === 400 || $apiStatusCode === 404) {
                return false;
            }

            if ($apiStatusCode === 200) {
                $apiResponse = file_get_contents("$this->ukValidationEndpoint/organisations/vat/check-vat-number/lookup/$vatNumber");
                $apiResponse = json_decode($apiResponse, true);

                return $apiResponse['target'];
            }

            throw new VATCheckUnavailableException("The UK VAT check service is currently unavailable (status code $apiStatusCode). Please try again later.");
        } else {
            $this->initSoapClient();
            $client = $this->soapClient;

            if ($client) {
                try {
                    return $client->checkVat([
                        'countryCode' => $countryCode,
                        'vatNumber' => $vatNumber,
                    ]);
                } catch (SoapFault $e) {
                    if (isset($this->config) && $this->config->get('vat_calculator.forward_soap_faults')) {
                        throw new VATCheckUnavailableException($e->getMessage(), $e->getCode(), $e->getPrevious());
                    }

                    return false;
                }
            }

            throw new VATCheckUnavailableException('The VAT check service is currently unavailable. Please try again later.');
        }
    }

    /**
     * @return void
     *
     * @throws VATCheckUnavailableException
     */
    public function initSoapClient()
    {
        if (is_object($this->soapClient) || $this->soapClient === false) {
            return;
        }

        // Set's default timeout time.
        $timeout = 30;

        if (isset($this->config) && $this->config->has('vat_calculator.soap_timeout')) {
            $timeout = $this->config->get('vat_calculator.soap_timeout');
        }

        $context = stream_context_create(['http' => ['timeout' => $timeout]]);

        try {
            $this->soapClient = new SoapClient(self::VAT_SERVICE_URL, ['stream_context' => $context]);
        } catch (SoapFault $e) {
            if (isset($this->config) && $this->config->get('vat_calculator.forward_soap_faults')) {
                throw new VATCheckUnavailableException($e->getMessage(), $e->getCode(), $e->getPrevious());
            }

            $this->soapClient = false;
        }
    }

    /**
     * @param  SoapClient  $soapClient
     */
    public function setSoapClient($soapClient)
    {
        $this->soapClient = $soapClient;
    }

    /**
     * @return $this
     *
     * @internal This method is not covered by our BC policy.
     */
    public function testing()
    {
        $this->ukValidationEndpoint = 'https://test-api.service.hmrc.gov.uk';

        return $this;
    }
}
