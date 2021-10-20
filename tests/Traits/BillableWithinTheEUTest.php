<?php

namespace Tests\Traits;

use Mockery as m;
use Mpociot\VatCalculator\Facades\VatCalculator;
use Mpociot\VatCalculator\Traits\BillableWithinTheEU;
use Mpociot\VatCalculator\VatCalculatorServiceProvider;
use PHPUnit\Framework\TestCase;

class BillableWithinTheEUTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        m::close();
    }

    protected function getPackageProviders($app)
    {
        return [VatCalculatorServiceProvider::class];
    }

    public function testTaxPercentZeroByDefault()
    {
        VatCalculator::shouldReceive('getTaxRateForCountry')
            ->once()
            ->with(null, false)
            ->andReturn(0);

        $billable = new BillableWithinTheEUTestStub();
        $taxPercent = $billable->getTaxPercent();
        $this->assertEquals(0, $taxPercent);
    }

    public function testTaxPercentGetsCalculated()
    {
        $countryCode = 'DE';
        $company = false;

        VatCalculator::shouldReceive('getTaxRateForCountry')
            ->once()
            ->with($countryCode, $company)
            ->andReturn(0.19);

        $billable = new BillableWithinTheEUTestStub();
        $billable->setTaxForCountry($countryCode, $company);
        $taxPercent = $billable->getTaxPercent();
        $this->assertEquals(19, $taxPercent);
    }

    public function testTaxPercentGetsCalculatedByUseTaxFrom()
    {
        $countryCode = 'DE';
        $company = false;

        VatCalculator::shouldReceive('getTaxRateForCountry')
            ->with($countryCode, $company)
            ->andReturn(0.19);

        $billable = new BillableWithinTheEUTestStub();
        $billable->useTaxFrom($countryCode);
        $taxPercent = $billable->getTaxPercent();
        $this->assertEquals(19, $taxPercent);
    }

    public function testTaxPercentGetsCalculatedByUseTaxFromAsBusinessCustomer()
    {
        $countryCode = 'DE';
        $company = true;

        VatCalculator::shouldReceive('getTaxRateForCountry')
            ->with($countryCode, $company)
            ->andReturn(0);

        $billable = new BillableWithinTheEUTestStub();
        $billable->useTaxFrom($countryCode)->asBusiness();
        $taxPercent = $billable->getTaxPercent();
        $this->assertEquals(0, $taxPercent);
    }

    public function testTaxPercentGetsCalculatedByUseTaxFromAsIndividual()
    {
        $countryCode = 'DE';
        $company = false;

        VatCalculator::shouldReceive('getTaxRateForCountry')
            ->once()
            ->with($countryCode, $company)
            ->andReturn(0.19);

        $billable = new BillableWithinTheEUTestStub();
        $billable->useTaxFrom($countryCode)->asIndividual();
        $taxPercent = $billable->getTaxPercent();
        $this->assertEquals(19, $taxPercent);
    }
}

class BillableWithinTheEUTestStub
{
    use BillableWithinTheEU;
}
