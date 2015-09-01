<?php

namespace Mpociot\VatCalculator\Tests;

use Mockery as m;

use Mpociot\VatCalculator\Facades\VatCalculator;
use PHPUnit_Framework_TestCase as PHPUnit;

class BillableWithinTheEUTraitTest extends PHPUnit
{
    public function tearDown()
    {
        m::close();
    }


    public function testTaxPercentZeroByDefault()
    {
        $billable = new BillableWithinTheEUTraitTestStub;
        $taxPercent = $billable->getTaxPercent();
        $this->assertEquals(0, $taxPercent);
    }


    public function testTaxPercentGetsCalculated()
    {
        $countryCode = 'DE';
        $company = false;

        VatCalculator::shouldReceive('getTaxRateForCountry')
        ->with( $countryCode, $company )
        ->andReturn( 0.19 );

        $billable = new BillableWithinTheEUTraitTestStub;
        $billable->setTaxForCountry( $countryCode, $company );
        $taxPercent = $billable->getTaxPercent();
        $this->assertEquals(19, $taxPercent);
    }

}


class BillableWithinTheEUTraitTestStub
{
    use \Mpociot\VatCalculator\Traits\BillableWithinTheEU;

}
