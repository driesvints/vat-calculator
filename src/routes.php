<?php

Route::get('vatcalculator/tax-rate-for-location/{country?}/{postal_code?}', 'Mpociot\VatCalculator\Http\Controller@getTaxRateForLocation');
Route::get('vatcalculator/calculate', 'Mpociot\VatCalculator\Http\Controller@calculateGrossPrice');
Route::get('vatcalculator/country-code', 'Mpociot\VatCalculator\Http\Controller@getCountryCode');
Route::get('vatcalculator/validate-vat-id/{vat_id}', 'Mpociot\VatCalculator\Http\Controller@validateVATID');
