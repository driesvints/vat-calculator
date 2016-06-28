# Changelog

#### v2.0.0 `2016-06-28`
- VAT rate detection now supports ZIP codes, to take edge cases into consideration 

#### v1.7.1 `2016-06-16`
- Fixed bug when using multiple Validator extensions in Laravel

#### v1.7.0 `2016-05-11`
- Added Cashier 6 support

#### v1.6.3 `2016-04-07`
- Added "shouldCollectVAT" method

#### v1.6.2 `2016-02-10`
- Added polish validation rule message

#### v1.6.1 `2016-01-14`
- Added support for `vat-taxrate` as an extra field for the JS frontend integration. Thanks @orottier

#### v1.6.0 `2016-01-12`
- Added support for setting your own "business country code" which will be used when selling to business customers inside your own country. Previous versions returned the wrong VAT rate (0%).

#### v1.5.5 `2016-01-11`
- Romania reduces VAT to 20% in 2016

#### v1.5.4 `2015-09-18`
- Fixed issue in vat_calculator.js

#### v1.5.3 `2015-09-18`
- Added valid_vat_id and calculate JS success callback

#### v1.5.2 `2015-09-18`
- Added greek tax rate

#### v1.5.1 `2015-09-16`
- Added spark asset publishing

#### v1.5.0 `2015-09-08`
- Added VAT Calculator JS

#### v1.4.4 `2015-09-07`
- The package can now be used without Laravel

#### v1.4.3 `2015-09-07`
- Updated standard VAT rate for Luxembourg

#### v1.4.2 `2015-09-06`
- Added facade explanation to README and fixed a bug in the ServiceProvider

#### v1.4.1 `2015-09-03`
- Fixed getClientIP tests

#### v1.4.0 `2015-09-02`
- Added IP to country lookup.

#### v1.3.0 `2015-09-02`
- Added Laravel Validator extension.

#### v1.2.1 `2015-09-01`
- Added chainable methods for the Laravel Cashier integration, to allow a more readable command.

#### v1.2.0 `2015-09-01`
- Added Laravel Cashier integration

#### v1.1.0 `2015-09-01`
- Added VAT number validation.

#### v1.0.0 `2015-09-01`
- First release.