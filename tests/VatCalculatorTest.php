<?php

namespace Mpociot\VatCalculator;

use Mockery as m;
use PHPUnit_Framework_TestCase as PHPUnit;

function file_get_contents($url)
{
    return VatCalculatorTest::$file_get_contents_result ?: \file_get_contents($url);
}

class VatCalculatorTest extends PHPUnit
{
    public static $file_get_contents_result;

    public function tearDown()
    {
        m::close();
    }

    public function testCalculateVatWithoutCountry()
    {
        $config = m::mock('Illuminate\Contracts\Config\Repository');

        $config->shouldReceive('has')
            ->once()
            ->with('vat_calculator.business_country_code')
            ->andReturn(false);

        $config->shouldReceive('has')
            ->once()
            ->with('vat_calculator.rules.')
            ->andReturn(false);

        $net = 25.00;

        $vatCalculator = new VatCalculator($config);
        $result = $vatCalculator->calculate($net);
        $this->assertEquals(25.00, $result);
    }

    public function testCalculateVatWithoutCountryAndConfig()
    {
        $net = 25.00;

        $vatCalculator = new VatCalculator();
        $result = $vatCalculator->calculate($net);
        $this->assertEquals(25.00, $result);
    }

    public function testCalculateVatWithPredefinedRules()
    {
        $net = 24.00;
        $countryCode = 'DE';

        $config = m::mock('Illuminate\Contracts\Config\Repository');
        $config->shouldReceive('get')
            ->never();

        $config->shouldReceive('has')
            ->once()
            ->with('vat_calculator.rules.DE')
            ->andReturn(false);

        $config->shouldReceive('has')
            ->once()
            ->with('vat_calculator.business_country_code')
            ->andReturn(false);

        $vatCalculator = new VatCalculator($config);
        $result = $vatCalculator->calculate($net, $countryCode);
        $this->assertEquals(28.56, $result);
        $this->assertEquals(0.19, $vatCalculator->getTaxRate());
        $this->assertEquals(4.56, $vatCalculator->getTaxValue());
    }

    public function testCalculateVatWithPredefinedRulesWithoutConfig()
    {
        $net = 24.00;
        $countryCode = 'DE';

        $vatCalculator = new VatCalculator();
        $result = $vatCalculator->calculate($net, $countryCode);
        $this->assertEquals(28.56, $result);
        $this->assertEquals(0.19, $vatCalculator->getTaxRate());
        $this->assertEquals(4.56, $vatCalculator->getTaxValue());
    }

    public function testCalculateVatWithPredefinedRulesOverwrittenByConfiguration()
    {
        $net = 24.00;
        $countryCode = 'DE';

        $taxKey = 'vat_calculator.rules.'.strtoupper($countryCode);

        $config = m::mock('Illuminate\Contracts\Config\Repository');
        $config->shouldReceive('get')
            ->once()
            ->with($taxKey, 0)
            ->andReturn(0.50);

        $config->shouldReceive('has')
            ->once()
            ->with($taxKey)
            ->andReturn(true);

        $config->shouldReceive('has')
            ->once()
            ->with('vat_calculator.business_country_code')
            ->andReturn(false);

        $vatCalculator = new VatCalculator($config);
        $result = $vatCalculator->calculate($net, $countryCode);
        $this->assertEquals(36.00, $result);
        $this->assertEquals(0.50, $vatCalculator->getTaxRate());
        $this->assertEquals(12.00, $vatCalculator->getTaxValue());
    }

    public function testCalculatVatWithCountryDirectSet()
    {
        $net = 24.00;
        $countryCode = 'DE';

        $config = m::mock('Illuminate\Contracts\Config\Repository');
        $config->shouldReceive('get')
            ->once()
            ->with('vat_calculator.rules.'.$countryCode, 0)
            ->andReturn(0.19);

        $config->shouldReceive('has')
            ->once()
            ->with('vat_calculator.rules.'.$countryCode)
            ->andReturn(true);

        $config->shouldReceive('has')
            ->once()
            ->with('vat_calculator.business_country_code')
            ->andReturn(false);

        $vatCalculator = new VatCalculator($config);
        $result = $vatCalculator->calculate($net, $countryCode);
        $this->assertEquals(28.56, $result);
        $this->assertEquals(0.19, $vatCalculator->getTaxRate());
        $this->assertEquals(4.56, $vatCalculator->getTaxValue());
    }

    public function testCalculatVatWithCountryDirectSetWithoutConfiguration()
    {
        $net = 24.00;
        $countryCode = 'DE';

        $vatCalculator = new VatCalculator();
        $result = $vatCalculator->calculate($net, $countryCode);
        $this->assertEquals(28.56, $result);
        $this->assertEquals(0.19, $vatCalculator->getTaxRate());
        $this->assertEquals(4.56, $vatCalculator->getTaxValue());
    }

    public function testCalculatVatWithCountryPreviousSet()
    {
        $net = 24.00;
        $countryCode = 'DE';

        $config = m::mock('Illuminate\Contracts\Config\Repository');
        $config->shouldReceive('get')
            ->once()
            ->with('vat_calculator.rules.'.$countryCode, 0)
            ->andReturn(0.19);

        $config->shouldReceive('has')
            ->once()
            ->with('vat_calculator.rules.'.$countryCode)
            ->andReturn(true);

        $config->shouldReceive('has')
            ->once()
            ->with('vat_calculator.business_country_code')
            ->andReturn(false);

        $vatCalculator = new VatCalculator($config);
        $vatCalculator->setCountryCode($countryCode);

        $result = $vatCalculator->calculate($net);
        $this->assertEquals(28.56, $result);
        $this->assertEquals(0.19, $vatCalculator->getTaxRate());
        $this->assertEquals(4.56, $vatCalculator->getTaxValue());
    }

    public function testCalculatVatWithCountryAndCompany()
    {
        $net = 24.00;
        $countryCode = 'DE';
        $postalCode = null;
        $company = true;

        $config = m::mock('Illuminate\Contracts\Config\Repository');
        $config->shouldReceive('get')
            ->never();

        $config->shouldReceive('has')
            ->once()
            ->with('vat_calculator.business_country_code')
            ->andReturn(false);

        $vatCalculator = new VatCalculator($config);
        $result = $vatCalculator->calculate($net, $countryCode, $postalCode, $company);
        $this->assertEquals(24.00, $result);
        $this->assertEquals(0, $vatCalculator->getTaxRate());
        $this->assertEquals(0, $vatCalculator->getTaxValue());
    }

    public function testCalculatVatWithCountryAndCompanySet()
    {
        $net = 24.00;
        $countryCode = 'DE';
        $company = true;

        $config = m::mock('Illuminate\Contracts\Config\Repository');
        $config->shouldReceive('get')
            ->never();

        $config->shouldReceive('has')
            ->once()
            ->with('vat_calculator.business_country_code')
            ->andReturn(false);

        $vatCalculator = new VatCalculator($config);
        $vatCalculator->setCompany($company);
        $result = $vatCalculator->calculate($net, $countryCode);
        $this->assertEquals(24.00, $result);
        $this->assertEquals(24.00, $vatCalculator->getNetPrice());
        $this->assertEquals(0, $vatCalculator->getTaxRate());
        $this->assertEquals(0, $vatCalculator->getTaxValue());
    }

    public function testCalculatVatWithCountryAndCompanyBothSet()
    {
        $net = 24.00;
        $countryCode = 'DE';
        $company = true;

        $config = m::mock('Illuminate\Contracts\Config\Repository');
        $config->shouldReceive('get')
            ->never();

        $config->shouldReceive('has')
            ->once()
            ->with('vat_calculator.business_country_code')
            ->andReturn(false);

        $vatCalculator = new VatCalculator($config);
        $vatCalculator->setCountryCode($countryCode);
        $vatCalculator->setCompany($company);
        $result = $vatCalculator->calculate($net);
        $this->assertEquals(24.00, $result);
        $this->assertEquals(0, $vatCalculator->getTaxRate());
        $this->assertEquals(0, $vatCalculator->getTaxValue());
    }

    public function testGetTaxRateForCountry()
    {
        $countryCode = 'DE';

        $config = m::mock('Illuminate\Contracts\Config\Repository');
        $config->shouldReceive('get')
            ->once()
            ->with('vat_calculator.rules.'.$countryCode, 0)
            ->andReturn(0.19);

        $config->shouldReceive('has')
            ->once()
            ->with('vat_calculator.rules.'.$countryCode)
            ->andReturn(true);

        $config->shouldReceive('has')
            ->once()
            ->with('vat_calculator.business_country_code')
            ->andReturn(false);

        $vatCalculator = new VatCalculator($config);
        $result = $vatCalculator->getTaxRateForLocation($countryCode);
        $this->assertEquals(0.19, $result);
    }

    public function testGetTaxRateForCountryAndCompany()
    {
        $countryCode = 'DE';
        $company = true;

        $config = m::mock('Illuminate\Contracts\Config\Repository');
        $config->shouldReceive('get')
            ->never();

        $config->shouldReceive('has')
            ->once()
            ->with('vat_calculator.business_country_code')
            ->andReturn(false);

        $vatCalculator = new VatCalculator($config);
        $result = $vatCalculator->getTaxRateForLocation($countryCode, null, $company);
        $this->assertEquals(0, $result);
    }

    public function testCanValidateValidVATNumber()
    {
        $config = m::mock('Illuminate\Contracts\Config\Repository');

        $config->shouldReceive('has')
            ->once()
            ->with('vat_calculator.business_country_code')
            ->andReturn(false);

        $result = new \stdClass();
        $result->valid = true;

        $vatCheck = $this->getMockFromWsdl(__DIR__.'/checkVatService.wsdl', 'VATService');
        $vatCheck->expects($this->any())
            ->method('checkVat')
            ->with([
                'countryCode' => 'DE',
                'vatNumber'   => '190098891',
            ])
            ->willReturn($result);

        $vatNumber = 'DE 190 098 891';
        $vatCalculator = new VatCalculator($config);
        $vatCalculator->setSoapClient($vatCheck);
        $result = $vatCalculator->isValidVATNumber($vatNumber);
        $this->assertTrue($result);
    }

    public function testCanValidateInvalidVATNumber()
    {
        $result = new \stdClass();
        $result->valid = false;

        $vatCheck = $this->getMockFromWsdl(__DIR__.'/checkVatService.wsdl', 'VATService');
        $vatCheck->expects($this->any())
            ->method('checkVat')
            ->with([
                'countryCode' => 'So',
                'vatNumber'   => 'meInvalidNumber',
            ])
            ->willReturn($result);

        $vatNumber = 'SomeInvalidNumber';
        $vatCalculator = new VatCalculator();
        $vatCalculator->setSoapClient($vatCheck);
        $result = $vatCalculator->isValidVATNumber($vatNumber);
        $this->assertFalse($result);
    }

    public function testValidateVATNumberReturnsFalseOnSoapFailure()
    {
        $vatCheck = $this->getMockFromWsdl(__DIR__.'/checkVatService.wsdl', 'VATService');
        $vatCheck->expects($this->any())
            ->method('checkVat')
            ->with([
                'countryCode' => 'So',
                'vatNumber'   => 'meInvalidNumber',
            ])
            ->willThrowException(new \SoapFault('Server', 'Something went wrong'));

        $vatNumber = 'SomeInvalidNumber';
        $vatCalculator = new VatCalculator();
        $vatCalculator->setSoapClient($vatCheck);
        $result = $vatCalculator->isValidVATNumber($vatNumber);
        $this->assertFalse($result);
    }

    public function testCannotValidateVATNumberWhenServiceIsDown()
    {
        $this->setExpectedException(\Mpociot\VatCalculator\Exceptions\VATCheckUnavailableException::class);

        $result = new \stdClass();
        $result->valid = false;

        $vatNumber = 'SomeInvalidNumber';
        $vatCalculator = new VatCalculator();
        $vatCalculator->setSoapClient(false);
        $vatCalculator->isValidVATNumber($vatNumber);
    }

    public function testCanResolveIPToCountry()
    {
        self::$file_get_contents_result = '1;DE;DEU;Deutschland';
        $vatCalculator = new VatCalculator();
        $country = $vatCalculator->getIPBasedCountry();
        $this->assertEquals('DE', $country);
    }

    public function testCanResolveInvalidIPToCountry()
    {
        self::$file_get_contents_result = '0';
        $vatCalculator = new VatCalculator();
        $country = $vatCalculator->getIPBasedCountry();
        $this->assertFalse($country);
    }

    public function testCanHandleIPServiceDowntime()
    {
        self::$file_get_contents_result = false;
        $_SERVER['REMOTE_ADDR'] = '';
        $vatCalculator = new VatCalculator();
        $country = $vatCalculator->getIPBasedCountry();
        $this->assertFalse($country);
    }

    public function testCompanyInBusinessCountryGetsValidVATRate()
    {
        $net = 24.00;
        $countryCode = 'DE';

        $config = m::mock('Illuminate\Contracts\Config\Repository');
        $config->shouldReceive('get')
            ->never();

        $config->shouldReceive('has')
            ->once()
            ->with('vat_calculator.rules.DE')
            ->andReturn(false);

        $config->shouldReceive('has')
            ->once()
            ->with('vat_calculator.business_country_code')
            ->andReturn(true);

        $config->shouldReceive('get')
            ->once()
            ->with('vat_calculator.business_country_code', '')
            ->andReturn($countryCode);

        $vatCalculator = new VatCalculator($config);
        $result = $vatCalculator->calculate($net, $countryCode, null, true);
        $this->assertEquals(28.56, $result);
        $this->assertEquals(0.19, $vatCalculator->getTaxRate());
        $this->assertEquals(4.56, $vatCalculator->getTaxValue());
    }

    public function testCompanyInBusinessCountryGetsValidVATRateDirectSet()
    {
        $net = 24.00;
        $countryCode = 'DE';

        $vatCalculator = new VatCalculator();
        $vatCalculator->setBusinessCountryCode('DE');
        $result = $vatCalculator->calculate($net, $countryCode, null, true);
        $this->assertEquals(28.56, $result);
        $this->assertEquals(0.19, $vatCalculator->getTaxRate());
        $this->assertEquals(4.56, $vatCalculator->getTaxValue());
    }

    public function testCompanyOutsideBusinessCountryGetsValidVATRate()
    {
        $net = 24.00;
        $countryCode = 'DE';

        $vatCalculator = new VatCalculator();
        $vatCalculator->setBusinessCountryCode('NL');
        $result = $vatCalculator->calculate($net, $countryCode, null, true);
        $this->assertEquals(24.00, $result);
        $this->assertEquals(0.00, $vatCalculator->getTaxRate());
        $this->assertEquals(0.00, $vatCalculator->getTaxValue());
    }

    public function testReturnsZeroForInvalidCountryCode()
    {
        $net = 24.00;
        $countryCode = 'XXX';

        $vatCalculator = new VatCalculator();
        $result = $vatCalculator->calculate($net, $countryCode, null, true);
        $this->assertEquals(24.00, $result);
        $this->assertEquals(0.00, $vatCalculator->getTaxRate());
        $this->assertEquals(0.00, $vatCalculator->getTaxValue());
    }

    public function testChecksPostalCodeForVATExceptions()
    {
        $net = 24.00;
        $vatCalculator = new VatCalculator();
        $postalCode = '27498'; // Heligoland
        $result = $vatCalculator->calculate($net, 'DE', $postalCode, false);
        $this->assertEquals(24.00, $result);
        $this->assertEquals(0.00, $vatCalculator->getTaxRate());
        $this->assertEquals(0.00, $vatCalculator->getTaxValue());

        $postalCode = '6691'; // Jungholz
        $result = $vatCalculator->calculate($net, 'AT', $postalCode, false);
        $this->assertEquals(28.56, $result);
        $this->assertEquals(0.19, $vatCalculator->getTaxRate());
        $this->assertEquals(4.56, $vatCalculator->getTaxValue());

        $postalCode = 'BFPO58'; // Dhekelia
        $result = $vatCalculator->calculate($net, 'GB', $postalCode, false);
        $this->assertEquals(28.56, $result);
        $this->assertEquals(0.19, $vatCalculator->getTaxRate());
        $this->assertEquals(4.56, $vatCalculator->getTaxValue());

        $postalCode = '9122'; // Madeira
        $result = $vatCalculator->calculate($net, 'PT', $postalCode, false);
        $this->assertEquals(29.28, $result);
        $this->assertEquals(0.22, $vatCalculator->getTaxRate());
        $this->assertEquals(5.28, $vatCalculator->getTaxValue());
    }

    public function testShouldCollectVAT()
    {
        $vatCalculator = new VatCalculator();
        $this->assertTrue($vatCalculator->shouldCollectVAT('DE'));
        $this->assertTrue($vatCalculator->shouldCollectVAT('NL'));
        $this->assertFalse($vatCalculator->shouldCollectVAT(''));
        $this->assertFalse($vatCalculator->shouldCollectVAT('XXX'));
    }

    public function testShouldCollectVATFromConfig()
    {
        $countryCode = 'TEST';
        $taxKey = 'vat_calculator.rules.'.strtoupper($countryCode);

        $config = m::mock('Illuminate\Contracts\Config\Repository');

        $config->shouldReceive('has')
            ->with($taxKey)
            ->andReturn(true);

        $config->shouldReceive('has')
            ->once()
            ->with('vat_calculator.business_country_code')
            ->andReturn(false);

        $vatCalculator = new VatCalculator($config);
        $this->assertTrue($vatCalculator->shouldCollectVAT($countryCode));
    }
}
