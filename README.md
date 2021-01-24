# Vat Calculator

<a href="https://github.com/mpociot/vat-calculator/actions">
    <img src="https://github.com/mpociot/vat-calculator/workflows/Tests/badge.svg" alt="Tests">
</a>
<a href="https://github.styleci.io/repos/41703624">
    <img src="https://github.styleci.io/repos/41703624/shield?style=flat" alt="Code Style">
</a>
<a href="https://packagist.org/packages/mpociot/vat-calculator">
    <img src="https://img.shields.io/packagist/v/mpociot/vat-calculator" alt="Latest Stable Version">
</a>
<a href="https://packagist.org/packages/mpociot/vat-calculator">
    <img src="https://img.shields.io/packagist/dt/mpociot/vat-calculator" alt="Total Downloads">
</a>

Handle all the hard stuff related to EU MOSS tax/vat regulations, the way it should be.
Can be used with **Laravel 5 / Cashier** &mdash; or **standalone**.

```php
// Easy to use!
$countryCode = VatCalculator::getIPBasedCountry();
VatCalculator::calculate( 24.00, $countryCode );
VatCalculator::calculate( 24.00, $countryCode, $postalCode );
VatCalculator::calculate( 71.00, 'DE', '41352', $isCompany = true );
VatCalculator::getTaxRateForLocation( 'NL' );
// Check validity of a VAT number
VatCalculator::isValidVATNumber('NL123456789B01');
```
## Contents

- [Installation](#installation)
	- [Standalone](#installation-standalone)
- [Usage](#usage)
	- [Calculate the gross price](#calculate-the-gross-price)
	- [Receive more information](#receive-more-information)
	- [Validate EU VAT numbers](#validate-eu-vat-numbers)
		- [Laravel Validator extension](#laravel-validator-extension)
	- [Get EU VAT number details](#vat-number-details)
	- [Cashier integration](#cashier-integration)
	- [Get the IP based country of your user](#get-ip-based-country)
	- [Frontend integration - vat_calculator.js](#frontend-integration)
		- [Integrating it in your payment form](#payment-form-integration)
		- [Extra fields](#extra-fields)
		- [Form attributes](#form-attributes)
		- [Form fields](#form-fields)
		- [Advanced usage](#advanced-usage)
		- [Preconfigured routes](#preconfigured-routes)
- [Configuration (optional)](#configuration)
- [Changelog](#changelog)
- [Maintainers](#maintainers)
- [License](#license)

<a name="installation"></a>
## Installation

In order to install the VAT Calculator, just run

```bash
$ composer require mpociot/vat-calculator
```
	
<a name="installation-standalone"></a>
### Standalone

You can also use this package without Laravel. Simply create a new instance of the VAT calculator and use it.
All documentation examples use the Laravel 5 facade code, so make sure not to call the methods as if they were static methods.

Example:

```php
use Mpociot\VatCalculator\VatCalculator;

$vatCalculator = new VatCalculator();
$vatCalculator->setBusinessCountryCode('DE');
$countryCode = $vatCalculator->getIPBasedCountry();
$grossPrice = $vatCalculator->calculate( 49.99, 'LU' );
```

<a name="usage"></a>
## Usage
<a name="calculate-the-gross-price"></a>
### Calculate the gross price
To calculate the gross price use the `calculate` method with a net price and a country code as parameters.

```php
$grossPrice = VatCalculator::calculate( 24.00, 'DE' );
```
The third parameter is the postal code of the customer.

As a fourth parameter, you can pass in a boolean indicating whether the customer is a company or a private person. If the customer is a company, which you should check by <a href="#validate-eu-vat-numbers">validating the VAT number</a>, the net price gets returned.


```php
$grossPrice = VatCalculator::calculate( 24.00, 'DE', '12345', $isCompany = true );
```
<a name="receive-more-information"></a>
### Receive more information
After calculating the gross price you can extract more information from the VatCalculator.

```php
$grossPrice = VatCalculator::calculate( 24.00, 'DE' ); // 28.56
$taxRate    = VatCalculator::getTaxRate(); // 0.19
$netPrice   = VatCalculator::getNetPrice(); // 24.00
$taxValue   = VatCalculator::getTaxValue(); // 4.56
```

<a name="validate-eu-vat-numbers"></a>
### Validate EU VAT numbers

Prior to validating your customers VAT numbers, you can use the `shouldCollectVAT` method to check if the country code requires you to collect VAT
in the first place.

```php
if (VatCalculator::shouldCollectVAT('DE')) {

}
```

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

<a name="vat-number-details"></a>
### Get EU VAT number details

To get the details of a VAT number, you can use the `getVATDetails` method.
The VAT number should be in a format specified by the [VIES](http://ec.europa.eu/taxation_customs/vies/faqvies.do#item_11).
The given VAT numbers will be truncated and non relevant characters / whitespace will automatically be removed.

This service relies on a third party SOAP API provided by the EU. If, for whatever reason, this API is unavailable a `VATCheckUnavailableException` will be thrown.

```php
try {
	$vat_details = VatCalculator::getVATDetails('NL 123456789 B01');
	print_r($vat_details);
	/* Outputs
	stdClass Object
	(
		[countryCode] => NL
		[vatNumber] => 123456789B01
		[requestDate] => 2017-04-06+02:00
		[valid] => false
		[name] => Name of the company
		[address] => Address of the company
	)
	*/
} catch( VATCheckUnavailableException $e ){
	// Please handle me
}
```

<a name="laravel-validator-extension"></a>
### Laravel Validator Extension
If you want to include the VAT number validation directly in your existing Form Requests / Validations, use the `vat_number` validation rule.

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

<a name="cashier-integration"></a>
### Cashier integration
If you want to use this package in combination with [Laravel Cashier](https://github.com/laravel/cashier/) you can let your billable model use the `BillableWithinTheEU` trait. Because this trait overrides the `getTaxPercent` method of the `Billable` trait, we have to explicitly tell our model to do so.

```php
use Laravel\Cashier\Billable;
use Mpociot\VatCalculator\Traits\BillableWithinTheEU;
use Laravel\Cashier\Contracts\Billable as BillableContract;

class User extends Model implements BillableContract
{
    use Billable, BillableWithinTheEU {
        BillableWithinTheEU::taxPercentage insteadof Billable;
    }

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

<a name="get-ip-based-country"></a>
## Get the IP based Country of your user(s)
Right now you'll need to show your users a way to select their country - probably a drop down - to use this country for the VAT calculation.

This package has a small helper function, that tries to lookup the Country of the user, based on the IP they have.

```php
$countryCode = VatCalculator::getIPBasedCountry();
```

The `$countryCode` will either be `false`, if the service is unavailable, or the country couldn't be looked up. Otherwise the variable contains the two-letter country code, which can be used to prefill the user selection.

<a name="frontend-integration"></a>
## Frontend integration &mdash; vat_calculator.js
Phew - so you know how to use this class, built your fancy payment form and now...? Well - you want to display the correct prices to your users and want it to update dynamically. So go ahead, add some routes, write some Javascript and in no time you'll be up and running, right?

Or you use the **built in routes** and **vat_calculator.js** library.

The VAT Calculator JS library will automatically: 

- Calculate taxes whenever the selected country value changes
- Automatically validate VAT-IDs / VAT numbers and use it for the calculation
- Prefill the user's country with the IP based country

The Javascript library has no dependencies on third party frameworks.

In order to use the Javascript helper you need to publish the package files first. Go ahead and type:

```bash
$ php artisan vendor:publish --provider="Mpociot\VatCalculator\VatCalculatorServiceProvider"
```

Now you have a file called `vat_calculator.js` in your `public/js` folder.

<a name="payment-form-integration"></a>
### Integrating it in your payment form

Add the published javascript file to your payment form.

```html
<head>
  ...
<script type="text/javascript" src="/js/vat_calculator.js"></script>
</head>
```

By default, the VAT Calculator JS script is looking for a form with the ID `payment-form`. 
This form needs a `data-amount` attribute specifying the amount to use for the tax calculation in **cents** (just like Stripe uses it).

So your form should look like this, when you would calculate the taxes for 24.99 €

```html
<form method="post" id="payment-form" data-amount="2499">
```

Next up, you need a dropdown to let your users select their billing country. This select field needs the `data-vat="country"` attribute, so that the VAT Calculator JS knows, where to look for country codes.

Since there are also quite a few VAT rate exceptions for specific regions or cities, it is highly recommended to add an input field to collect postal codes.
This field needs a `data-vat="postal-code"` attribute.

And last but not least, to automatically validate VAT Numbers / VAT IDs you can have an input field with the `data-vat="vat_number"` attribute specified.

So your form will look like this:

```html

<form method="POST" id="payment-form" data-amount="2499">

        <div class="form-row">
            <label>
                <span>Country</span>
                <select data-vat="country">
                    <option value="US">United States</option>
                    <option value="GB">United Kingdom</option>
                    <option value="DE">Germany</option>
                    <option value="FR">France</option>
                    <option value="IT">Italy</option>
                    <option value="ES">Spain</option>
                    <option value="CA">Canada</option>
                    <option value="AU">Australia</option>
                </select>
            </label>
        </div>
        
        <div class="form-row">
            <label>
                <span>Postal Code</span>
                <input data-vat="postal-code"/>
            </label>
        </div>
        
        <div class="form-row">
            <label>
                <span>VAT Number</span>
                <input data-vat="vat-number"/>
            </label>
        </div>
</form>
```
<a name="extra-fields"></a>
### Extra fields

To display the live tax calculation, you can use the classes `vat-subtotal`, `vat-taxrate`, `vat-taxes` and `vat-total` on any DOM element and VAT Calculator JS will automatically set the inner HTML content for you.

Example:

```html
<strong>Subtotal</strong>: € <span class="vat-subtotal"></span>
<strong>Tax rate</strong>: <span class="vat-taxrate"></span>%
<strong>Taxes</strong>: € <span class="vat-taxes"></span>
<strong>Total</strong>: € <span class="vat-total"></span>
```

<a name="form-attributes"></a>
### Form attributes
Attribute  | Description | Required
------------- | ------------- | ----------
`data-amount`  | Use this attribute on the `form` you want to use for live calculation. It's the price in **cent** used for the calculation. | Yes

<a name="form-fields"></a>
### Form fields
In order to calculate the right taxes, you need to add some extra inputs to your payment form.
All these fields need to have a `data-vat` attribute. You need to include at least the `country`.

Attribute  | Description | Required
------------- | ------------- | ----------
`country`  | Customer’s country (2-letter ISO code). | Yes
`postal-code`  | Customer's postal code | No **Highly recommended**
`vat-number`  | Billing VAT number | No

<a name="advanced-usage"></a>
### Advanced usage

#### Use a different form selector
Use `VATCalculator.init('#my-selector')` to initialize the live calculation on a different form.

#### Use a custom formatter function to modify calculation result HTML
Use `VATCalculator.setCurrencyFormatter` to use a different method to format the calculated values for the HTML output.
This function will receive the calculation result as a parameter.

Example:

```javascript
VATCalculator.setCurrencyFormatter(function(value){
    return value.toFixed(2) + ' €';
});
```

#### Trigger calculation manually
Call `VATCalculator.calculate()` to trigger the calculation manually. For example when you change the `data-amount` attribute on your form.

<a name="preconfigured-routes"></a>
### Preconfigured routes

In order for VAT Calculator JS to work properly, these routes will be added to your application. If you don't want to use the Javascript library, you can of course disable the routes in the <a href="#configuration">configuration</a> file.

Method | Route | Usage
-------|-------|-------
`GET` | `vatcalculator/tax-rate-for-location/{country}/{postal-code}` | Returns the VAT / tax rate for the given country (2-letter ISO code).
`GET` | `vatcalculator/country-code` | Returns the 2-letter ISO code based from the IP address.
`GET` | `vatcalculator/validate-vat-id/{vat_id}` | Validates the given VAT ID
`GET` | `vatcalculator/calculate` | Calculates the gross price based on the parameters: `netPrice`, `country` and `vat_number`

<a name="configuration"></a>
## Configuration

By default, the VAT Calculator has all EU VAT rules predefined, so that it can easily be updated, if it changes for a specific country.

If you need to define other VAT rates, you can do so by publishing the configuration and add more rules.

The configuration file also determines whether you want to use the VAT Calculator JS routes or not.

**Important:** Be sure to set your business country code in the configuration file, to get correct VAT calculation when selling to business customers in your own country.

To publish the configuration files, run the `vendor:publish` command

```bash
$ php artisan vendor:publish --provider="Mpociot\VatCalculator\VatCalculatorServiceProvider"
```

This will create a `vat_calculator.php` in your config directory.

## Changelog

Check out the [CHANGELOG](CHANGELOG.md) in this repository for all the recent changes.

## Maintainers

Vat Calculator is developed and maintained by [Marcel Pociot](https://pociot.dev) & [Dries Vints](https://driesvints.com).

## License

Vat Calculator is open-sourced software licensed under [the MIT license](LICENSE.md).
