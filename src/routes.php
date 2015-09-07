<?php

Route::get('vatcalculator/tax-rate-for-country/{country?}', 'Mpociot\VatCalculator\Http\Controller@getTaxRateForCountry' );
Route::get('vatcalculator/calculate/{country?}', 'Mpociot\VatCalculator\Http\Controller@calculateGrossPrice' );
Route::get('vatcalculator/country-code', 'Mpociot\VatCalculator\Http\Controller@getCountryCode' );
Route::get('vatcalculator/validate-vat-id/{vat_id}', 'Mpociot\VatCalculator\Http\Controller@validateVATID' );