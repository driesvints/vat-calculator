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

    public function testCalculatVatWithoutCountry()
    {
        $config = m::mock('Illuminate\Contracts\Config\Repository');
        $config->shouldReceive('get')
            ->once()
            ->andReturn(0);

        $net = 25.00;

        $vatCalculator = new VatCalculator($config);
        $result        = $vatCalculator->calculate($net);
        $this->assertEquals(25.00, $result);
    }

    public function testCalculatVatWithCountryDirectSet()
    {
        $net         = 24.00;
        $countryCode = 'DE';

        $config = m::mock('Illuminate\Contracts\Config\Repository');
        $config->shouldReceive('get')
            ->once()
            ->with('vat_calculator.rules.' . $countryCode, 0)
            ->andReturn(0.19);

        $vatCalculator = new VatCalculator($config);
        $result        = $vatCalculator->calculate($net, $countryCode);
        $this->assertEquals(28.56, $result);
        $this->assertEquals(0.19, $vatCalculator->getTaxRate());
        $this->assertEquals(4.56, $vatCalculator->getTaxValue());
    }

    public function testCalculatVatWithCountryPreviousSet()
    {
        $net         = 24.00;
        $countryCode = 'DE';

        $config = m::mock('Illuminate\Contracts\Config\Repository');
        $config->shouldReceive('get')
            ->once()
            ->with('vat_calculator.rules.' . $countryCode, 0)
            ->andReturn(0.19);

        $vatCalculator = new VatCalculator($config);
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

        $config = m::mock('Illuminate\Contracts\Config\Repository');
        $config->shouldReceive('get')
            ->never();

        $vatCalculator = new VatCalculator($config);
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

        $config = m::mock('Illuminate\Contracts\Config\Repository');
        $config->shouldReceive('get')
            ->never();

        $vatCalculator = new VatCalculator($config);
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

        $config = m::mock('Illuminate\Contracts\Config\Repository');
        $config->shouldReceive('get')
            ->never();

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
            ->with('vat_calculator.rules.' . $countryCode, 0)
            ->andReturn(0.19);

        $vatCalculator = new VatCalculator($config);
        $result        = $vatCalculator->getTaxRateForCountry($countryCode);
        $this->assertEquals(0.19, $result);
    }

    public function testGetTaxRateForCountryAndCompany()
    {
        $countryCode = 'DE';
        $company     = true;

        $config = m::mock('Illuminate\Contracts\Config\Repository');
        $config->shouldReceive('get')
            ->never();

        $vatCalculator = new VatCalculator($config);
        $result        = $vatCalculator->getTaxRateForCountry($countryCode, $company);
        $this->assertEquals(0, $result);
    }
}
