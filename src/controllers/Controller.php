<?php

namespace Mpociot\VatCalculator\Http;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Response;
use Mpociot\VatCalculator\Exceptions\VATCheckUnavailableException;
use Mpociot\VatCalculator\VatCalculator;
use Illuminate\Contracts\Config\Repository as ConfigRepository;

class Controller extends BaseController
{
    /**
     * @var VatCalculator
     */
    private $calculator;

    public function __construct(ConfigRepository $configRepository)
    {
        $this->calculator = new \Mpociot\VatCalculator\VatCalculator($configRepository);
    }

    /**
     * Returns the tax rate for the given country code and postal code.
     *
     * @return \Illuminate\Http\Response
     */
    public function getTaxRateForLocation($countryCode = null, $postalCode = null)
    {
        return [
            'tax_rate' => $this->calculator->getTaxRateForLocation($countryCode, $postalCode),
        ];
    }

    /**
     * Returns the tax rate for the given country.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function calculateGrossPrice(Request $request)
    {
        if (!$request->has('netPrice')) {
            return Response::json([
                'error' => "The 'netPrice' parameter is missing",
            ], 422);
        }

        $valid_vat_id = null;
        $valid_company = false;
        if ($request->has('vat_number')) {
            $valid_company = $this->validateVATID($request->get('vat_number'));
            $valid_company = $valid_company['is_valid'];
            $valid_vat_id = $valid_company;
        }

        return [
            'gross_price'   => $this->calculator->calculate($request->get('netPrice'), $request->get('country'), $request->get('postal_code'), $valid_company),
            'net_price'     => $this->calculator->getNetPrice(),
            'tax_rate'      => $this->calculator->getTaxRate(),
            'tax_value'     => $this->calculator->getTaxValue(),
            'valid_vat_id'  => $valid_vat_id,
        ];
    }

    /**
     * Returns the tax rate for the given country.
     *
     * @return \Illuminate\Http\Response
     */
    public function getCountryCode()
    {
        return [
            'country_code' => $this->calculator->getIPBasedCountry(),
        ];
    }

    /**
     * Returns the tax rate for the given country.
     *
     * @param string $vat_id
     *
     * @throws \Mpociot\VatCalculator\Exceptions\VATCheckUnavailableException
     *
     * @return \Illuminate\Http\Response
     */
    public function validateVATID($vat_id)
    {
        try {
            $isValid = $this->calculator->isValidVATNumber($vat_id);
            $message = '';
        } catch (VATCheckUnavailableException $e) {
            $isValid = false;
            $message = $e->getMessage();
        }

        return [
            'vat_id'   => $vat_id,
            'is_valid' => $isValid,
            'message'  => $message,
        ];
    }
}
