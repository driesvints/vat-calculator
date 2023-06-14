<?php

namespace Mpociot\VatCalculator\Rules;

use Illuminate\Contracts\Validation\Rule;
use Mpociot\VatCalculator\Exceptions\VATCheckUnavailableException;
use Mpociot\VatCalculator\Facades\VatCalculator;

class ValidVatNumber implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     */
    public function passes($attribute, $value): bool
    {
        try {
            return VatCalculator::isValidVATNumber($value);
        } catch (VATCheckUnavailableException $e) {
            return false;
        }
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return ':attribute is not a valid VAT ID number.';
    }
}
