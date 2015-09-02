VatCalculator
================

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://travis-ci.org/mpociot/vat-calculator.svg)](https://travis-ci.org/mpociot/vat-calculator)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/mpociot/vat-calculator/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/mpociot/vat-calculator/?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/adecb98a-8484-48cb-be13-803decc475bc/mini.png)](https://insight.sensiolabs.com/projects/adecb98a-8484-48cb-be13-803decc475bc)

VAT / Tax calculation for Laravel 5 / Cashier. Fully compatible with the new EU MOSS reverse charge processing.

```php
// Easy to use!
VatCalculator::calculate( 24.00, 'DE' );
VatCalculator::calculate( 71.00, 'DE', $isCompany = true );
VatCalculator::getTaxRateForCountry( 'NL' );
// Check validity of a VAT number
VatCalculator::isValidVATNumber('NL123456789B01');
```
## Contents

- [Installation](#installation)
- [Usage](#usage)
	- [Calculate the gross price](#calculate-the-gross-price)
	- [Receive more information](#receive-more-information)
	- [Validate EU VAT numbers](#validate-eu-vat-numbers)
		- [Laravel Validator extension](#laravel-validator-extension)
	- [Cashier integration](#cashier-integration)
- [Configuration (optional)](#configuration)
- [Changelog](#changelog)
- [License](#license)

<a name="installation" />
## Installation

In order to install the VAT Calculator, just run

```bash
$ composer require mpociot/vat-calculator
```

Then in your `config/app.php` add 

    Mpociot\VatCalculator\VatCalculatorServiceProvider::class
    
in the `providers` array.
    
The `VatCalculator` Facade will be installed automatically within the Service Provider.

<a name="usage" />
## Usage
<a name="calculate-the-gross-price" />
### Calculate the gross price
To calculate the gross price use the `calculate` method with a net price and a country code as paremeters.

```php
$grossPrice = VatCalculator::calculate( 24.00, 'DE' );
```
As a third parameter, you can pass in a boolean indicating wether the customer is a company or a private person.


```php
$grossPrice = VatCalculator::calculate( 24.00, 'DE', $isCompany = true );
```
<a name="receive-more-information" />
### Receive more information
After calculating the gross price you can extract more informations from the VatCalculator.

```php
$grossPrice = VatCalculator::calculate( 24.00, 'DE' ); // 28.56
$taxRate    = VatCalculator::getTaxRate(); // 0.19
$netPrice   = VatCalculator::getNetPrice(); // 24.00
$taxValue   = VatCalculator::getTaxValue(); // 4.56
```

<a name="validate-eu-vat-numbers" />
### Validate EU VAT numbers
To validate your customers VAT numbers, you can use the `isValidVATNumber` method.
The VAT number should be in a format specified by the [VIES](http://ec.europa.eu/taxation_customs/vies/faqvies.do#item_11).
The given VAT numbers will be truncated and non relevant characters / whitespace will automatically be removed.

This service relies on a third party SOAP API provided by the EU. If, for whatever reason, this API is unavailable a `VATCheckUnavailableException` will be thrown.

```php
try {
	$validVAT = VatCalculator::isValidVATNumber('NL 123456789 B01');
} catch( VATCheckUnavailableException $e ){
	// Please handle me
}
```

<a name="laravel-validator-extension" />
### Laravel Validator Extension
If you want to include the VAT number validation directly in your existing Form Requests / Validations, use the `vat_number` validtion rule.

Example:

```php
$rules = array(
    'first_name'  => 'required',
    'last_name'   => 'required',
    'company_vat' => 'vat_number'
);

$validator = Validator::make(Input::all(), $rules);
```

**Important:** The validator extension returns `false` when the VAT ID Check SOAP API is unavailable.

<a name="cashier-integration" />
### Cashier integration
If you want to use this module in combination with [Laravel Cashier](https://github.com/laravel/cashier/) you can let your billable model use the `BillableWithinTheEU` trait.

```php
use Laravel\Cashier\Billable;
use Mpociot\VatCalculator\Traits\BillableWithinTheEU;
use Laravel\Cashier\Contracts\Billable as BillableContract;

class User extends Model implements BillableContract
{
    use Billable, BillableWithinTheEU;

    protected $dates = ['trial_ends_at', 'subscription_ends_at'];
}
```

By using the `BillableWithinTheEU` trait, your billable model has new methods to set the tax rate for the billable model.

Set everything in one command:

- `setTaxForCountry($countryCode, $company = false)`

Or use the more readable, chainable approach:

- `useTaxFrom($countryCode)` &mdash; Use the given countries tax rate
- `asIndividual()` &mdash; The billable model is not a company (default) 
- `asBusiness()` &mdash; The billable model is a valid company

So in order to set the correct tax percentage prior to subscribing your customer, consider the following workflow:

```php
$user = User::find(1);

// For individuals use:
$user->useTaxFrom('NL');

// For business customers with a valid VAT ID, use:
$user->useTaxFrom('NL')->asBusiness();

$user->subscription('monthly')->create($creditCardToken);
```

<a name="configuration" />
## Configuration (optional)

By default, the VAT Calculator has all EU VAT rules predefined, so that it can easily be updated, if it changes for a specific country.

If you need to define other VAT rates, you can do so by publishing the configuration and add more rules.

To publish the configuration files, run the `vendor:publish` command

```bash
$ php artisan vendor:publish --provider="Mpociot\VatCalculator\VatCalculatorServiceProvider"
```

This will create a `vat_calculator.php` in your config directory.
This configuration holds all EU relevant tax rules.

<a name="changelog" />
## Changelog
Please see [CHANGELOG](CHANGELOG.md) for more information.


<a name="license" />
## License
This library is licensed under the MIT license. Please see [License file](LICENSE.md) for more information.
