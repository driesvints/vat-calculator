<?php

namespace Mpociot\VatCalculator\Validators;

use Illuminate\Validation\Validator;
use Mpociot\VatCalculator\Exceptions\VATCheckUnavailableException;
use Mpociot\VatCalculator\Facades\VatCalculator;

class VatCalculatorValidatorExtension extends Validator
{
    /**
     * Creates a new instance of ValidatorExtension.
     */
    public function __construct($translator, $data, $rules, $messages, array $customAttributes = [])
    {
        // Set custom validation error messages
        if (!isset($messages['vat_number'])) {
            $messages['vat_number'] = $translator->get(
                'vatnumber-validator::validation.vat_number'
            );
        }
        parent::__construct($translator, $data, $rules, $messages, $customAttributes);
    }

    /**
     * Usage: vat_number.
     *
     * @param string $attribute
     * @param mixed  $value
     * @param array  $parameters
     *
     * @return bool
     */
    public function validateVatNumber($attribute, $value, $parameters)
    {
        try {
            return VatCalculator::isValidVATNumber($value);
        } catch (VATCheckUnavailableException $e) {
            return false;
        }
    }
}
