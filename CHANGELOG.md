# Changelog

This changelog follows [the Keep a Changelog standard](https://keepachangelog.com) (as of 2.4.2).


## [Unreleased](https://github.com/driesvints/vat-calculator/compare/3.0.1...3.x)


## [3.0.1 (2022-01-10)](https://github.com/driesvints/vat-calculator/compare/3.0.0...3.0.1)

### Fixed
- Fix some Italian postal codes ([#137](https://github.com/driesvints/vat-calculator/pull/137))


## [3.0.0 (2021-10-20)](https://github.com/driesvints/vat-calculator/compare/2.5.2...3.0.0)

### Changed
- Drop support for PHP 7.2 and below ([#131](https://github.com/driesvints/vat-calculator/pull/131))
- Drop support for Laravel 5.8 and below ([#131](https://github.com/driesvints/vat-calculator/pull/131))
- Refactor validation rule ([#133](https://github.com/driesvints/vat-calculator/pull/133))

### Fixed
- Remove incorrect VAT rates ([#130](https://github.com/driesvints/vat-calculator/pull/130))

### Removed
- Remove front-end functionality ([#128](https://github.com/driesvints/vat-calculator/pull/128))
- Remove translations ([5097c75](https://github.com/driesvints/vat-calculator/commit/5097c752dffa3ca823010816078805017fec2c75))
- Remove IP lookup functionality ([#129](https://github.com/driesvints/vat-calculator/pull/129))


## [2.5.2 (2021-05-26)](https://github.com/driesvints/vat-calculator/compare/2.5.1...2.5.2)

### Changed
- Using get_headers to avoid 404 status code with file_get_contents ([#119](https://github.com/driesvints/vat-calculator/pull/119))
- Throw unavailable exception for UK VAT API ([3c937b4](https://github.com/driesvints/vat-calculator/commit/3c937b4cde8e3a8936eecf4ce56395f2daa6baa6))


## [2.5.1 (2021-05-25)](https://github.com/driesvints/vat-calculator/compare/2.5.0...2.5.1)

### Fixed
- Fix invalid UK VAT number validation ([6e674e4](https://github.com/driesvints/vat-calculator/commit/6e674e41d413c219f5e66ba53946a8138f88e6bc))


## [2.5.0 (2021-05-24)](https://github.com/driesvints/vat-calculator/compare/2.4.2...2.5.0)

### Added
- Validate UK VAT numbers ([#116](https://github.com/driesvints/vat-calculator/pull/116))


## [2.4.2 (2021-01-24)](https://github.com/driesvints/vat-calculator/compare/2.4.1...2.4.2)

### Fixed
- Revert temporary german VAT change ([#102](https://github.com/driesvints/vat-calculator/pull/102))
- Use HTTPS for links ([#105](https://github.com/driesvints/vat-calculator/pull/105))
- Fix IPV6 resolving ([#83](https://github.com/driesvints/vat-calculator/pull/83), [3c6b16d](https://github.com/driesvints/vat-calculator/commit/3c6b16d819a1f2fff61fce16b625a184d1c2fac2))


## 2.0.0 (2016-06-28)
- VAT rate detection now supports ZIP codes, to take edge cases into consideration 


## 1.7.1 (2016-06-16)
- Fixed bug when using multiple Validator extensions in Laravel


## 1.7.0 (2016-05-11)
- Added Cashier 6 support


## 1.6.3 (2016-04-07)
- Added "shouldCollectVAT" method


## 1.6.2 (2016-02-10)
- Added polish validation rule message


## 1.6.1 (2016-01-14)
- Added support for `vat-taxrate` as an extra field for the JS frontend integration. Thanks @orottier


## 1.6.0 (2016-01-12)
- Added support for setting your own "business country code" which will be used when selling to business customers inside your own country. Previous versions returned the wrong VAT rate (0%).


## 1.5.5 (2016-01-11)
- Romania reduces VAT to 20% in 2016


## 1.5.4 (2015-09-18)
- Fixed issue in vat_calculator.js


## 1.5.3 (2015-09-18)
- Added valid_vat_id and calculate JS success callback


## 1.5.2 (2015-09-18)
- Added greek tax rate


## 1.5.1 (2015-09-16)
- Added spark asset publishing


## 1.5.0 (2015-09-08)
- Added VAT Calculator JS


## 1.4.4 (2015-09-07)
- The package can now be used without Laravel


## 1.4.3 (2015-09-07)
- Updated standard VAT rate for Luxembourg


## 1.4.2 (2015-09-06)
- Added facade explanation to README and fixed a bug in the ServiceProvider


## 1.4.1 (2015-09-03)
- Fixed getClientIP tests


## 1.4.0 (2015-09-02)
- Added IP to country lookup.


## 1.3.0 (2015-09-02)
- Added Laravel Validator extension.


## 1.2.1 (2015-09-01)
- Added chainable methods for the Laravel Cashier integration, to allow a more readable command.


## 1.2.0 (2015-09-01)
- Added Laravel Cashier integration


## 1.1.0 (2015-09-01)
- Added VAT number validation.


## 1.0.0 (2015-09-01)
- First release.
