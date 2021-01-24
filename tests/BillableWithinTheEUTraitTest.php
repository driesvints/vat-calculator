<?php

namespace Tests;

use Mockery as m;
use Mpociot\VatCalculator\Facades\VatCalculator;
use PHPUnit\Framework\TestCase;

class BillableWithinTheEUTraitTest extends TestCase
{
    protected function tearDown(): void
    {
        VatCalculator::clearResolvedInstances();

        m::close();
    }

    public function testTaxPercentZeroByDefault()
    {
        VatCalculator::shouldReceive('getTaxRateForCountry')
            ->once()
            ->with(null, false)
            ->andReturn(0);

        $billable = new BillableWithinTheEUTraitTestStub();
        $taxPercent = $billable->getTaxPercent();
        $this->assertEquals(0, $taxPercent);
    }

    public function testTaxPercentGetsCalculated()
    {
        m::close();
        $countryCode = 'DE';
        $company = false;

        VatCalculator::shouldReceive('getTaxRateForCountry')
            ->once()
            ->with($countryCode, $company)
            ->andReturn(0.19);

        $billable = new BillableWithinTheEUTraitTestStub();
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

        $billable = new BillableWithinTheEUTraitTestStub();
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

        $billable = new BillableWithinTheEUTraitTestStub();
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

        $billable = new BillableWithinTheEUTraitTestStub();
        $billable->useTaxFrom($countryCode)->asIndividual();
        $taxPercent = $billable->getTaxPercent();
        $this->assertEquals(19, $taxPercent);
    }
}

class BillableWithinTheEUTraitTestStub
{
    use \Mpociot\VatCalculator\Traits\BillableWithinTheEU;
}
