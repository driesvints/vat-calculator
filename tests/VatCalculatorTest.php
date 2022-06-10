<?php

namespace Tests;

use Mockery as m;
use Mpociot\VatCalculator\VatCalculator;
use PHPUnit\Framework\TestCase;

class VatCalculatorTest extends TestCase
{
    public static $file_get_contents_result;

    protected function tearDown(): void
    {
        parent::tearDown();

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

        $taxKey = 'vat_calculator.rules.' . strtoupper($countryCode);

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
            ->with('vat_calculator.rules.' . $countryCode, 0)
            ->andReturn(0.19);

        $config->shouldReceive('has')
            ->once()
            ->with('vat_calculator.rules.' . $countryCode)
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
            ->with('vat_calculator.rules.' . $countryCode, 0)
            ->andReturn(0.19);

        $config->shouldReceive('has')
            ->once()
            ->with('vat_calculator.rules.' . $countryCode)
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

    public function testGetTaxRateForLocationWithCountry()
    {
        $countryCode = 'DE';

        $config = m::mock('Illuminate\Contracts\Config\Repository');
        $config->shouldReceive('get')
            ->once()
            ->with('vat_calculator.rules.' . $countryCode, 0)
            ->andReturn(0.19);

        $config->shouldReceive('has')
            ->once()
            ->with('vat_calculator.rules.' . $countryCode)
            ->andReturn(true);

        $config->shouldReceive('has')
            ->once()
            ->with('vat_calculator.business_country_code')
            ->andReturn(false);

        $vatCalculator = new VatCalculator($config);
        $result = $vatCalculator->getTaxRateForLocation($countryCode);
        $this->assertEquals(0.19, $result);
    }

    public function testGetTaxRateForCountry()
    {
        $countryCode = 'DE';

        $config = m::mock('Illuminate\Contracts\Config\Repository');
        $config->shouldReceive('get')
            ->once()
            ->with('vat_calculator.rules.' . $countryCode, 0)
            ->andReturn(0.19);

        $config->shouldReceive('has')
            ->once()
            ->with('vat_calculator.rules.' . $countryCode)
            ->andReturn(true);

        $config->shouldReceive('has')
            ->once()
            ->with('vat_calculator.business_country_code')
            ->andReturn(false);

        $vatCalculator = new VatCalculator($config);
        $result = $vatCalculator->getTaxRateForCountry($countryCode);
        $this->assertEquals(0.19, $result);
    }

    public function testGetTaxRateForLocationWithCountryAndCompany()
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
        $result = $vatCalculator->getTaxRateForCountry($countryCode, $company);
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

        $vatCheck = $this->getMockFromWsdl(__DIR__ . '/checkVatService.wsdl', 'VATService');
        $vatCheck->expects($this->any())
            ->method('checkVat')
            ->with([
                'countryCode' => 'DE',
                'vatNumber' => '190098891',
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

        $vatCheck = $this->getMockFromWsdl(__DIR__ . '/checkVatService.wsdl', 'VATService');
        $vatCheck->expects($this->any())
            ->method('checkVat')
            ->with([
                'countryCode' => 'So',
                'vatNumber' => 'meInvalidNumber',
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
        $vatCheck = $this->getMockFromWsdl(__DIR__ . '/checkVatService.wsdl', 'VATService');
        $vatCheck->expects($this->any())
            ->method('checkVat')
            ->with([
                'countryCode' => 'So',
                'vatNumber' => 'meInvalidNumber',
            ])
            ->willThrowException(new \SoapFault('Server', 'Something went wrong'));

        $vatNumber = 'SomeInvalidNumber';
        $vatCalculator = new VatCalculator();
        $vatCalculator->setSoapClient($vatCheck);
        $result = $vatCalculator->isValidVATNumber($vatNumber);
        $this->assertFalse($result);
    }

    public function testValidateVATNumberReturnsFalseOnSoapFailureWithoutForwarding()
    {
        $vatCheck = $this->getMockFromWsdl(__DIR__ . '/checkVatService.wsdl', 'VATService');
        $vatCheck->expects($this->any())
            ->method('checkVat')
            ->with([
                'countryCode' => 'So',
                'vatNumber' => 'meInvalidNumber',
            ])
            ->willThrowException(new \SoapFault('Server', 'Something went wrong'));

        $config = m::mock('Illuminate\Contracts\Config\Repository');
        $config->shouldReceive('has')
            ->once()
            ->with('vat_calculator.business_country_code')
            ->andReturn(false);
        $config->shouldReceive('get')
            ->once()
            ->with('vat_calculator.forward_soap_faults')
            ->andReturn(false);

        $vatNumber = 'SomeInvalidNumber';
        $vatCalculator = new VatCalculator($config);
        $vatCalculator->setSoapClient($vatCheck);
        $result = $vatCalculator->isValidVATNumber($vatNumber);
        $this->assertFalse($result);
    }

    public function testValidateVATNumberThrowsExceptionOnSoapFailure()
    {
        $this->expectException(\Mpociot\VatCalculator\Exceptions\VATCheckUnavailableException::class);

        $vatCheck = $this->getMockFromWsdl(__DIR__ . '/checkVatService.wsdl', 'VATService');
        $vatCheck->expects($this->any())
            ->method('checkVat')
            ->with([
                'countryCode' => 'So',
                'vatNumber' => 'meInvalidNumber',
            ])
            ->willThrowException(new \SoapFault('Server', 'Something went wrong'));

        $config = m::mock('Illuminate\Contracts\Config\Repository');
        $config->shouldReceive('has')
            ->once()
            ->with('vat_calculator.business_country_code')
            ->andReturn(false);
        $config->shouldReceive('get')
            ->once()
            ->with('vat_calculator.forward_soap_faults')
            ->andReturn(true);

        $vatNumber = 'SomeInvalidNumber';
        $vatCalculator = new VatCalculator($config);
        $vatCalculator->setSoapClient($vatCheck);
        $vatCalculator->isValidVATNumber($vatNumber);
    }

    public function testCannotValidateVATNumberWhenServiceIsDown()
    {
        $this->expectException(\Mpociot\VatCalculator\Exceptions\VATCheckUnavailableException::class);

        $result = new \stdClass();
        $result->valid = false;

        $vatNumber = 'SomeInvalidNumber';
        $vatCalculator = new VatCalculator();
        $vatCalculator->setSoapClient(false);
        $vatCalculator->isValidVATNumber($vatNumber);
    }

    public function testCanValidateValidUKVATNumber()
    {
        $config = m::mock('Illuminate\Contracts\Config\Repository');

        $config->shouldReceive('has')
            ->once()
            ->with('vat_calculator.business_country_code')
            ->andReturn(false);

        $result = new \stdClass();
        $result->valid = true;

        $vatNumber = 'GB 553557881';
        $vatCalculator = new VatCalculator($config);
        $result = $vatCalculator->testing()->isValidVATNumber($vatNumber);
        $this->assertTrue($result);
    }

    public function testCanValidateInvalidUKVATNumber()
    {
        $config = m::mock('Illuminate\Contracts\Config\Repository');

        $config->shouldReceive('has')
            ->once()
            ->with('vat_calculator.business_country_code')
            ->andReturn(false);

        $result = new \stdClass();
        $result->valid = true;

        $vatNumber = 'GB Invalid';
        $vatCalculator = new VatCalculator($config);
        $result = $vatCalculator->testing()->isValidVATNumber($vatNumber);
        $this->assertFalse($result);
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

    public function testPostalCodesWithoutExceptionsGetStandardRate()
    {
        $net = 24.00;
        $vatCalculator = new VatCalculator();

        // Invalid post code
        $postalCode = 'IGHJ987ERT35';
        $result = $vatCalculator->calculate($net, 'ES', $postalCode, false);
        //Expect standard rate for Spain
        $this->assertEquals(29.04, $result);
        $this->assertEquals(0.21, $vatCalculator->getTaxRate());
        $this->assertEquals(5.04, $vatCalculator->getTaxValue());

        // Valid BE post code
        $postalCode = '2000';
        $result = $vatCalculator->calculate($net, 'BE', $postalCode, false);
        //Expect standard rate for BE
        $this->assertEquals(29.04, $result);
        $this->assertEquals(0.21, $vatCalculator->getTaxRate());
        $this->assertEquals(5.04, $vatCalculator->getTaxValue());
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
        $taxKey = 'vat_calculator.rules.' . strtoupper($countryCode);

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

    public function testCalculateNetPriceWithoutCountry()
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

        $gross = 25.00;

        $vatCalculator = new VatCalculator($config);
        $result = $vatCalculator->calculateNet($gross);
        $this->assertEquals(25.00, $result);
    }

    public function testCalculateNetPriceWithoutCountryAndConfig()
    {
        $gross = 25.00;

        $vatCalculator = new VatCalculator();
        $result = $vatCalculator->calculateNet($gross);
        $this->assertEquals(25.00, $result);
    }

    public function testCalculateNetPriceWithPredefinedRules()
    {
        $gross = 28.56;
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
        $result = $vatCalculator->calculateNet($gross, $countryCode);
        $this->assertEquals(24.00, $result);
        $this->assertEquals(0.19, $vatCalculator->getTaxRate());
        $this->assertEquals(4.56, $vatCalculator->getTaxValue());
    }

    public function testCalculateNetPriceWithPredefinedRulesWithoutConfig()
    {
        $gross = 28.56;
        $countryCode = 'DE';

        $vatCalculator = new VatCalculator();
        $result = $vatCalculator->calculateNet($gross, $countryCode);
        $this->assertEquals(24.00, $result);
        $this->assertEquals(0.19, $vatCalculator->getTaxRate());
        $this->assertEquals(4.56, $vatCalculator->getTaxValue());
    }

    public function testCalculateNetPriceWithPredefinedRulesOverwrittenByConfiguration()
    {
        $gross = 36.00;
        $countryCode = 'DE';

        $taxKey = 'vat_calculator.rules.' . strtoupper($countryCode);

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
        $result = $vatCalculator->calculateNet($gross, $countryCode);
        $this->assertEquals(24.00, $result);
        $this->assertEquals(0.50, $vatCalculator->getTaxRate());
        $this->assertEquals(12.00, $vatCalculator->getTaxValue());
    }

    public function testCalculateNetPriceWithCountryDirectSet()
    {
        $gross = 28.56;
        $countryCode = 'DE';

        $config = m::mock('Illuminate\Contracts\Config\Repository');
        $config->shouldReceive('get')
            ->once()
            ->with('vat_calculator.rules.' . $countryCode, 0)
            ->andReturn(0.19);

        $config->shouldReceive('has')
            ->once()
            ->with('vat_calculator.rules.' . $countryCode)
            ->andReturn(true);

        $config->shouldReceive('has')
            ->once()
            ->with('vat_calculator.business_country_code')
            ->andReturn(false);

        $vatCalculator = new VatCalculator($config);
        $result = $vatCalculator->calculateNet($gross, $countryCode);
        $this->assertEquals(24.00, $result);
        $this->assertEquals(0.19, $vatCalculator->getTaxRate());
        $this->assertEquals(4.56, $vatCalculator->getTaxValue());
    }

    public function testCalculateNetPriceWithCountryDirectSetWithoutConfiguration()
    {
        $gross = 28.56;
        $countryCode = 'DE';

        $vatCalculator = new VatCalculator();

        $result = $vatCalculator->calculateNet($gross, $countryCode);
        $this->assertEquals(24.00, $result);
        $this->assertEquals(0.19, $vatCalculator->getTaxRate());
        $this->assertEquals(4.56, $vatCalculator->getTaxValue());
    }

    public function testCalculateNetPriceWithCountryPreviousSet()
    {
        $gross = 28.56;
        $countryCode = 'DE';

        $config = m::mock('Illuminate\Contracts\Config\Repository');
        $config->shouldReceive('get')
            ->once()
            ->with('vat_calculator.rules.' . $countryCode, 0)
            ->andReturn(0.19);

        $config->shouldReceive('has')
            ->once()
            ->with('vat_calculator.rules.' . $countryCode)
            ->andReturn(true);

        $config->shouldReceive('has')
            ->once()
            ->with('vat_calculator.business_country_code')
            ->andReturn(false);

        $vatCalculator = new VatCalculator($config);
        $vatCalculator->setCountryCode($countryCode);

        $result = $vatCalculator->calculateNet($gross);
        $this->assertEquals(24.00, $result);
        $this->assertEquals(0.19, $vatCalculator->getTaxRate());
        $this->assertEquals(4.56, $vatCalculator->getTaxValue());
    }

    public function testCalculateNetPriceWithCountryAndCompany()
    {
        $gross = 28.56;
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
        $result = $vatCalculator->calculateNet($gross, $countryCode, $postalCode, $company);
        $this->assertEquals(28.56, $result);
        $this->assertEquals(0, $vatCalculator->getTaxRate());
        $this->assertEquals(0, $vatCalculator->getTaxValue());
    }

    public function testCalculateNetPriceWithCountryAndCompanySet()
    {
        $gross = 24.00;
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
        $result = $vatCalculator->calculateNet($gross, $countryCode);
        $this->assertEquals(24.00, $result);
        $this->assertEquals(24.00, $vatCalculator->getNetPrice());
        $this->assertEquals(0, $vatCalculator->getTaxRate());
        $this->assertEquals(0, $vatCalculator->getTaxValue());
    }

    public function testCalculateNetPriceWithCountryAndCompanyBothSet()
    {
        $gross = 24.00;
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
        $result = $vatCalculator->calculateNet($gross);
        $this->assertEquals(24.00, $result);
        $this->assertEquals(0, $vatCalculator->getTaxRate());
        $this->assertEquals(0, $vatCalculator->getTaxValue());
    }

    public function testCalculateHighVatType()
    {
        $gross = 24.00;
        $countryCode = 'NL';
        $company = false;
        $type = 'high';
        $postalCode = null;

        $vatCalculator = new VatCalculator();
        $result = $vatCalculator->calculate($gross, $countryCode, $postalCode, $company, $type);

        $this->assertEquals(29.04, $result);
    }

    public function testCalculateLowVatType()
    {
        $gross = 24.00;
        $countryCode = 'NL';
        $company = false;
        $type = 'low';
        $postalCode = null;

        $vatCalculator = new VatCalculator();
        $result = $vatCalculator->calculate($gross, $countryCode, $postalCode, $company, $type);

        $this->assertEquals(26.16, $result);
    }

    /**
     * @covers VatCalculator::isValidVatNumberFormat
     */
    public function testIsValidVatNumberFormat()
    {
        $valid = [
            'ATU12345678',
            'BE0123456789',
            'BE1234567891',
            'BG123456789',
            'BG1234567890',
            'CY12345678X',
            'CZ12345678',
            'DE123456789',
            'DE 190 098 891',
            'DK12345678',
            'DK99 99 99 99',
            'EE123456789',
            'EL123456789',
            'ESX12345678',
            'FI12345678',
            'FR12345678901',
            'FRA2345678901',
            'FRAB345678901',
            'FR1B345678901',
            'GB999999973',
            'HU12345678',
            'HR12345678901',
            'IE1234567X',
            'IT12345678901',
            'LT123456789',
            'LU12345678',
            'LV12345678901',
            'MT12345678',
            'NL123456789B12',
            'NL 123456789 B01',
            'PL1234567890',
            'PT123456789',
            'RO123456789',
            'SE123456789012',
            'SI12345678',
            'SK1234567890',
        ];

        $vatCalculator = new VatCalculator();
        foreach ($valid as $format) {
            $this->assertTrue($vatCalculator->isValidVatNumberFormat($format), "{$format} did not pass validation.");
        }

        $invalid = [
            '',
            'ATU1234567',
            'BE012345678',
            'BE123456789',
            'BG1234567',
            'CY1234567X',
            'CZ1234567',
            'DE12345678',
            'DK1234567',
            'EE12345678',
            'EL12345678',
            'ESX1234567',
            'FI1234567',
            'FR1234567890',
            'GB99999997',
            'HU1234567',
            'HR1234567890',
            'IE123456X',
            'IT1234567890',
            'LT12345678',
            'LU1234567',
            'LV1234567890',
            'MT1234567',
            'NL12345678B12',
            'PL123456789',
            'PT12345678',
            'RO1',  // Romania has a really weird VAT format...
            'SE12345678901',
            'SI1234567',
            'SK123456789',

            // valid number but with prefix
            'invalid_prefix_GB999999973',
            'invalid_prefix_IE1234567X',
            'invalid_prefix_ESB1234567C',
            'invalid_prefix_BE0123456789',
            'invalid_prefix_MT12345678',
            'invalid_prefix_LT123456789',

            // valid number but with suffix
            'IE1234567X_invalid_suffix',
            'ESB1234567C_invalid_suffix',
            'BE0123456789_invalid_suffix',
            'MT12345678_invalid_suffix',
            'LT123456789_invalid_suffix',
        ];

        foreach ($invalid as $format) {
            $isValid = $vatCalculator->isValidVatNumberFormat($format);
            $this->assertFalse($isValid, "{$format} passed validation, but shouldn't.");
        }
    }
}
