<?php

return [

    /*
    |--------------------------------------------------------------------------
    | VAT rules
    |--------------------------------------------------------------------------
    |
    | If you need to apply custom VAT rules for a specific country code,
    | use this array to define the rules that fit your needs. All EU
    | VAT rules are preconfigured inside but can be overwritten
    | at this point
    |
    */

    'rules' => [
        // 'XX' => 0.17,
    ],

    /*
    |--------------------------------------------------------------------------
    | Business country code
    |--------------------------------------------------------------------------
    |
    | This should be the country code where your business is located.
    | The business country code is used to calculate the correct VAT rate
    | when charging a B2B (company) customer inside your business country.
    |
    */

    'business_country_code' => '',

    'forward_soap_faults' => false,

];
