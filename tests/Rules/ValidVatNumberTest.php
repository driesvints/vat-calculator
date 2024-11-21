<?php

namespace Tests\Rules;

use Illuminate\Support\Facades\Validator;
use Mockery as m;
use Mpociot\VatCalculator\Exceptions\VATCheckUnavailableException;
use Mpociot\VatCalculator\Facades\VatCalculator;
use Mpociot\VatCalculator\Rules\ValidVatNumber;
use Mpociot\VatCalculator\VatCalculatorServiceProvider;
use Orchestra\Testbench\TestCase;

class ValidVatNumberTest extends TestCase
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

    public function test_validates_vat_number()
    {
        $vatNumber = 'DE 190 098 891';

        VatCalculator::shouldReceive('isValidVATNumber')
            ->with($vatNumber)
            ->once()
            ->andReturnTrue();

        $validator = Validator::make(
            ['vat_number' => $vatNumber],
            ['vat_number' => ['required', new ValidVatNumber]]
        );

        $this->assertTrue($validator->passes());
    }

    public function test_validates_invalid_vat_number()
    {
        $vatNumber = '098 891';

        VatCalculator::shouldReceive('isValidVATNumber')
            ->with($vatNumber)
            ->once()
            ->andReturnFalse();

        $validator = Validator::make(
            ['vat_number' => $vatNumber],
            ['vat_number' => ['required', new ValidVatNumber]]
        );

        $this->assertTrue($validator->fails());
    }

    public function test_validates_unavailable_vat_number_check()
    {
        $vatNumber = '098 891';

        VatCalculator::shouldReceive('isValidVATNumber')
            ->with($vatNumber)
            ->once()
            ->andThrow(new VATCheckUnavailableException);

        $validator = Validator::make(
            ['vat_number' => $vatNumber],
            ['vat_number' => ['required', new ValidVatNumber]]
        );

        $this->assertTrue($validator->fails());
    }

    public function test_default_error_message_works()
    {
        $vatNumber = '098 891';

        VatCalculator::shouldReceive('isValidVATNumber')
            ->with($vatNumber)
            ->once()
            ->andThrow(new VATCheckUnavailableException);

        $validator = Validator::make(
            ['vat_number' => $vatNumber],
            ['vat_number' => ['required', new ValidVatNumber]]
        );

        $errors = $validator->errors()->toArray();

        $this->assertArrayHasKey('vat_number', $errors);
        $this->assertEquals($errors['vat_number'][0], 'vat number is not a valid VAT ID number.');
    }
}
