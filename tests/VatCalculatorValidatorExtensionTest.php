<?php

namespace Mpociot\VatCalculator\tests;

use Illuminate\Support\Facades\Validator;
use Mockery as m;
use Mpociot\VatCalculator\Exceptions\VATCheckUnavailableException;
use Mpociot\VatCalculator\Facades\VatCalculator;
use Mpociot\VatCalculator\Validators\VatCalculatorValidatorExtension;
use Mpociot\VatCalculator\VatCalculatorServiceProvider;
use Orchestra\Testbench\TestCase;

class VatCalculatorValidatorExtensionTest extends TestCase
{
    protected $translator;
    protected $data;
    protected $rules;
    protected $messages;

    public function tearDown()
    {
        parent::tearDown();
        m::close();
        VatCalculator::clearResolvedInstances();
    }
    
    protected function getPackageProviders($app)
    {
        return [VatCalculatorServiceProvider::class];
    }

    public function testValidatesVATNumber()
    {
        $vatNumber = 'DE 190 098 891';

        VatCalculator::shouldReceive('isValidVATNumber')
            ->with($vatNumber)
            ->once()
            ->andReturnTrue();

        $validator = Validator::make(['vat_number' => $vatNumber], ['vat_number' => 'required|vat_number']);

        $this->assertTrue($validator->passes());
    }

    public function testValidatesInvalidVATNumber()
    {
        $vatNumber = '098 891';

        VatCalculator::shouldReceive('isValidVATNumber')
            ->with($vatNumber)
            ->once()
            ->andReturnFalse();

        $validator = Validator::make(['vat_number' => $vatNumber], ['vat_number' => 'required|vat_number']);

        $this->assertTrue($validator->fails());
    }

    public function testValidatesUnavailableVATNumberCheck()
    {
        $vatNumber = '098 891';

        VatCalculator::shouldReceive('isValidVATNumber')
            ->with($vatNumber)
            ->once()
            ->andThrow(new VATCheckUnavailableException());

        $validator = Validator::make(['vat_number' => $vatNumber], ['vat_number' => 'required|vat_number']);

        $this->assertTrue($validator->fails());
    }

    public function testDefaultErrorMessageWorks()
    {
        $vatNumber = '098 891';

        VatCalculator::shouldReceive('isValidVATNumber')
            ->with($vatNumber)
            ->once()
            ->andThrow(new VATCheckUnavailableException());

        $validator = Validator::make(['vat_number' => $vatNumber], ['vat_number' => 'required|vat_number']);

        $errors = $validator->errors()->toArray();

        $this->assertArrayHasKey('vat_number', $errors);
        $this->assertEquals($errors['vat_number'][0], 'vat number is not a valid VAT ID number.');
    }
}
