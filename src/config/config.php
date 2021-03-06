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
    | Predefined routes
    |--------------------------------------------------------------------------
    |
    | The VAT calculator comes with a number of useful predefined routes
    | that allow you to use the VAT calculator JS toolkit. If you
    | don't want the routes to be registered, set this variable
    | to false.
    |
    */

    'use_routes' => true,

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
