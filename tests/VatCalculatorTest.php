<?php

namespace Mpociot\VatCalculator\Tests;

use Mockery as m;

use Mpociot\VatCalculator\VatCalculator;
use PHPUnit_Framework_TestCase as PHPUnit;

class VatCalculatorTest extends PHPUnit
{
    public function tearDown()
    {
        m::close();
    }

    public function testCalculateVatWithoutCountry()
    {
        $app    = m::mock('App');
        $config = m::mock('Illuminate\Contracts\Config\Repository');
        $config->shouldReceive('has')
            ->once()
            ->andReturn(true);

        $config->shouldReceive('get')
            ->once()
            ->andReturn(0);
        $app->shouldReceive('make')->with('Illuminate\Contracts\Config\Repository')->andReturn($config);

        $net = 25.00;

        $vatCalculator = new VatCalculator($app);
        $result        = $vatCalculator->calculate($net);
        $this->assertEquals(25.00, $result);
    }

    public function testCalculateVatWithPredefinedRules()
    {
        $net         = 24.00;
        $countryCode = 'DE';

        $app    = m::mock('App');
        $config = m::mock('Illuminate\Contracts\Config\Repository');
        $config->shouldReceive('get')
            ->never();

        $config->shouldReceive('has')
            ->once()
            ->andReturn(false);

        $app->shouldReceive('make')->with('Illuminate\Contracts\Config\Repository')->andReturn($config);

        $vatCalculator = new VatCalculator($app);
        $result        = $vatCalculator->calculate($net, $countryCode);
        $this->assertEquals(28.56, $result);
        $this->assertEquals(0.19, $vatCalculator->getTaxRate());
        $this->assertEquals(4.56, $vatCalculator->getTaxValue());
    }

    public function testCalculateVatWithPredefinedRulesOverwrittenByConfiguration()
    {
        $net         = 24.00;
        $countryCode = 'DE';

        $taxKey = 'vat_calculator.rules.' . strtoupper($countryCode);

        $app    = m::mock('App');
        $config = m::mock('Illuminate\Contracts\Config\Repository');
        $config->shouldReceive('get')
            ->once()
            ->with($taxKey, 0)
            ->andReturn(0.50);

        $config->shouldReceive('has')
            ->once()
            ->andReturn(true);

        $app->shouldReceive('make')->with('Illuminate\Contracts\Config\Repository')->andReturn($config);

        $vatCalculator = new VatCalculator($app);
        $result        = $vatCalculator->calculate($net, $countryCode);
        $this->assertEquals(36.00, $result);
        $this->assertEquals(0.50, $vatCalculator->getTaxRate());
        $this->assertEquals(12.00, $vatCalculator->getTaxValue());
    }

    public function testCalculatVatWithCountryDirectSet()
    {
        $net         = 24.00;
        $countryCode = 'DE';

        $app    = m::mock('App');
        $config = m::mock('Illuminate\Contracts\Config\Repository');
        $config->shouldReceive('get')
            ->once()
            ->with('vat_calculator.rules.' . $countryCode, 0)
            ->andReturn(0.19);

        $config->shouldReceive('has')
            ->once()
            ->andReturn(true);
        $app->shouldReceive('make')->with('Illuminate\Contracts\Config\Repository')->andReturn($config);

        $vatCalculator = new VatCalculator($app);
        $result        = $vatCalculator->calculate($net, $countryCode);
        $this->assertEquals(28.56, $result);
        $this->assertEquals(0.19, $vatCalculator->getTaxRate());
        $this->assertEquals(4.56, $vatCalculator->getTaxValue());
    }

    public function testCalculatVatWithCountryPreviousSet()
    {
        $net         = 24.00;
        $countryCode = 'DE';

        $app    = m::mock('App');
        $config = m::mock('Illuminate\Contracts\Config\Repository');
        $config->shouldReceive('get')
            ->once()
            ->with('vat_calculator.rules.' . $countryCode, 0)
            ->andReturn(0.19);

        $config->shouldReceive('has')
            ->once()
            ->andReturn(true);
        $app->shouldReceive('make')->with('Illuminate\Contracts\Config\Repository')->andReturn($config);

        $vatCalculator = new VatCalculator($app);
        $vatCalculator->setCountryCode($countryCode);

        $result = $vatCalculator->calculate($net);
        $this->assertEquals(28.56, $result);
        $this->assertEquals(0.19, $vatCalculator->getTaxRate());
        $this->assertEquals(4.56, $vatCalculator->getTaxValue());
    }

    public function testCalculatVatWithCountryAndCompany()
    {
        $net         = 24.00;
        $countryCode = 'DE';
        $company     = true;

        $app    = m::mock('App');
        $config = m::mock('Illuminate\Contracts\Config\Repository');
        $config->shouldReceive('get')
            ->never();

        $config->shouldReceive('has')
            ->never();

        $app->shouldReceive('make')->with('Illuminate\Contracts\Config\Repository')->andReturn($config);

        $vatCalculator = new VatCalculator($app);
        $result        = $vatCalculator->calculate($net, $countryCode, $company);
        $this->assertEquals(24.00, $result);
        $this->assertEquals(0, $vatCalculator->getTaxRate());
        $this->assertEquals(0, $vatCalculator->getTaxValue());
    }

    public function testCalculatVatWithCountryAndCompanySet()
    {
        $net         = 24.00;
        $countryCode = 'DE';
        $company     = true;

        $app    = m::mock('App');
        $config = m::mock('Illuminate\Contracts\Config\Repository');
        $config->shouldReceive('get')
            ->never();

        $app->shouldReceive('make')->with('Illuminate\Contracts\Config\Repository')->andReturn($config);

        $vatCalculator = new VatCalculator($app);
        $vatCalculator->setCompany($company);
        $result = $vatCalculator->calculate($net, $countryCode);
        $this->assertEquals(24.00, $result);
        $this->assertEquals(0, $vatCalculator->getTaxRate());
        $this->assertEquals(0, $vatCalculator->getTaxValue());
    }

    public function testCalculatVatWithCountryAndCompanyBothSet()
    {
        $net         = 24.00;
        $countryCode = 'DE';
        $company     = true;

        $app    = m::mock('App');
        $config = m::mock('Illuminate\Contracts\Config\Repository');
        $config->shouldReceive('get')
            ->never();

        $config->shouldReceive('has')
            ->never();

        $app->shouldReceive('make')->with('Illuminate\Contracts\Config\Repository')->andReturn($config);

        $vatCalculator = new VatCalculator($app);
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

        $app    = m::mock('App');
        $config = m::mock('Illuminate\Contracts\Config\Repository');
        $config->shouldReceive('get')
            ->once()
            ->with('vat_calculator.rules.' . $countryCode, 0)
            ->andReturn(0.19);

        $config->shouldReceive('has')
            ->once()
            ->andReturn(true);
        $app->shouldReceive('make')->with('Illuminate\Contracts\Config\Repository')->andReturn($config);


        $vatCalculator = new VatCalculator($app);
        $result        = $vatCalculator->getTaxRateForCountry($countryCode);
        $this->assertEquals(0.19, $result);
    }

    public function testGetTaxRateForCountryAndCompany()
    {
        $countryCode = 'DE';
        $company     = true;

        $app    = m::mock('App');
        $config = m::mock('Illuminate\Contracts\Config\Repository');
        $config->shouldReceive('get')
            ->never();

        $config->shouldReceive('has')
            ->never();

        $app->shouldReceive('make')->with('Illuminate\Contracts\Config\Repository')->andReturn($config);


        $vatCalculator = new VatCalculator($app);
        $result        = $vatCalculator->getTaxRateForCountry($countryCode, $company);
        $this->assertEquals(0, $result);
    }

    public function testCanValidateValidVATNumber()
    {
        $app    = m::mock('App');
        $config = m::mock('Illuminate\Contracts\Config\Repository');

        $result        = new \stdClass();
        $result->valid = true;

        $app->shouldReceive('make')->with('Illuminate\Contracts\Config\Repository')->andReturn($config);

        $vatCheck = $this->getMockFromWsdl(__DIR__ . '/checkVatService.wsdl', 'VATService');
        $vatCheck->expects($this->any())
            ->method('checkVat')
            ->with([
                'countryCode' => "DE",
                'vatNumber'   => "190098891"
            ])
            ->willReturn($result);

        $vatNumber     = "DE 190 098 891";
        $vatCalculator = new VatCalculator($app);
        $vatCalculator->setSoapClient($vatCheck);
        $result = $vatCalculator->isValidVATNumber($vatNumber);
        $this->assertTrue($result);
    }

    public function testCanValidateInvalidVATNumber()
    {
        $app    = m::mock('App');
        $config = m::mock('Illuminate\Contracts\Config\Repository');

        $result        = new \stdClass();
        $result->valid = false;

        $app->shouldReceive('make')->with('Illuminate\Contracts\Config\Repository')->andReturn($config);

        $vatCheck = $this->getMockFromWsdl(__DIR__ . '/checkVatService.wsdl', 'VATService');
        $vatCheck->expects($this->any())
            ->method('checkVat')
            ->with([
                'countryCode' => "So",
                'vatNumber'   => "meInvalidNumber"
            ])
            ->willReturn($result);

        $vatNumber     = "SomeInvalidNumber";
        $vatCalculator = new VatCalculator($app);
        $vatCalculator->setSoapClient($vatCheck);
        $result = $vatCalculator->isValidVATNumber($vatNumber);
        $this->assertFalse($result);
    }

    public function testValidateVATNumberReturnsFalseOnSoapFailure()
    {
        $app    = m::mock('App');
        $config = m::mock('Illuminate\Contracts\Config\Repository');

        $app->shouldReceive('make')->with('Illuminate\Contracts\Config\Repository')->andReturn($config);

        $vatCheck = $this->getMockFromWsdl(__DIR__ . '/checkVatService.wsdl', 'VATService');
        $vatCheck->expects($this->any())
            ->method('checkVat')
            ->with([
                'countryCode' => "So",
                'vatNumber'   => "meInvalidNumber"
            ])
            ->willThrowException( new \SoapFault("Server","Something went wrong") );

        $vatNumber     = "SomeInvalidNumber";
        $vatCalculator = new VatCalculator($app);
        $vatCalculator->setSoapClient($vatCheck);
        $result = $vatCalculator->isValidVATNumber($vatNumber);
        $this->assertFalse($result);
    }

    public function testCannotValidateVATNumberWhenServiceIsDown()
    {
        $app    = m::mock('App');
        $config = m::mock('Illuminate\Contracts\Config\Repository');

        $this->setExpectedException( \Mpociot\VatCalculator\Exceptions\VATCheckUnavailableException::class );

        $result        = new \stdClass();
        $result->valid = false;

        $app->shouldReceive('make')->with('Illuminate\Contracts\Config\Repository')->andReturn($config);

        $vatNumber     = "SomeInvalidNumber";
        $vatCalculator = new VatCalculator($app);
        $vatCalculator->setSoapClient( false );
        $vatCalculator->isValidVATNumber($vatNumber);
    }
}
