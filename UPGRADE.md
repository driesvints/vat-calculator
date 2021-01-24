## Upgrade Guide

### Upgrading from v1 to v2

Version 2 of the VAT Calculator provides a new method to get a more precise VAT rate result.
It's recommended to use the new `getTaxRateForLocation` method instead of `getTaxRateForCountry`.
 
This method expects 3 arguments:

- country code - The country code of the customer
- postal code - The postal code of the customer
- company - Flag to indicate if the customer is a company
