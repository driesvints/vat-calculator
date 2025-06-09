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
        // Simple country code => rate mapping...
        // 'XX' => 0.17,

        // Country code with different rates and exceptions...
        // 'YY' => [
        //     'rate' => 0.20,
        //     'rates' => [
        //         'high' => 0.20,
        //         'low' => 0.09,
        //     ],
        //     'exceptions' => [
        //         'City' => 0.19,
        //     ],
        // ],
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

    /*
    |--------------------------------------------------------------------------
    | Enable SOAP fault exception throwing
    |--------------------------------------------------------------------------
    |
    | By default, SOAP faults for the VIES VAT API checks are handled
    | gracefully by returning them as false. However, you can enable
    | this setting to throw them as exceptions instead.
    |
    */

    'forward_soap_faults' => false,

    /*
    |--------------------------------------------------------------------------
    | Change the SOAP timeout
    |--------------------------------------------------------------------------
    |
    | By default, SOAP aborts the request to VIES after 30 seconds.
    | If you do not want to wait that long, you can reduce the timeout.
    | The timeout is specified in seconds.
    |
    */

    'soap_timeout' => 30,

    'hmrc' => [
        'client_id' => env('HMRC_CLIENT_ID'),
        'client_secret' => env('HMRC_CLIENT_SECRET'),
    ],

];
