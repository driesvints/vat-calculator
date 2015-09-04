<?php

namespace Mpociot\VatCalculator\Tests;

use Mockery as m;

use Mpociot\VatCalculator\Exceptions\VATCheckUnavailableException;
use Mpociot\VatCalculator\Facades\VatCalculator;
use Mpociot\VatCalculator\Validators\VatCalculatorValidatorExtension;
use PHPUnit_Framework_TestCase as PHPUnit;

class VatCalculatorValidatorExtensionTest extends PHPUnit
{
    protected $translator;
    protected $data;
    protected $rules;
    protected $messages;
    protected $testDefaultErrorMessage = 'This is a test error message for :attribute.';

    public function tearDown()
    {
        m::close();
        VatCalculator::clearResolvedInstances();
    }

    public function setUp()
    {
        $this->translator = m::mock('Symfony\Component\Translation\TranslatorInterface');
        $this->translator->shouldReceive('get')
            ->with('vatnumber-validator::validation.vat_number')
            ->andReturn($this->testDefaultErrorMessage);
        $this->translator->shouldReceive('trans')
            ->andReturnUsing(function($arg) { return $arg; });
        $this->rules = array(
            'customer_vat' => 'required|vat_number',
        );
        $this->messages = array();

    }

    public function testValidatesVATNumber()
    {
        $vatNumber     = "DE 190 098 891";
        $this->data = array(
            'customer_vat' => $vatNumber
        );
        VatCalculator::shouldReceive('isValidVATNumber')
            ->with( $vatNumber )
            ->andReturn( true );
        $validator = new VatCalculatorValidatorExtension(
            $this->translator,
            $this->data,
            $this->rules,
            $this->messages
        );
        $this->assertTrue($validator->passes());
    }

    public function testValidatesInvalidVATNumber()
    {
        $vatNumber     = "098 891";
        $this->data = array(
            'customer_vat' => $vatNumber
        );
        VatCalculator::shouldReceive('isValidVATNumber')
            ->with( $vatNumber )
            ->andReturn( false );
        $validator = new VatCalculatorValidatorExtension(
            $this->translator,
            $this->data,
            $this->rules,
            $this->messages
        );
        $this->assertTrue($validator->fails());
    }

    public function testValidatesUnavailableVATNumberCheck()
    {
        $vatNumber     = "098 891";
        $this->data = array(
            'customer_vat' => $vatNumber
        );
        VatCalculator::shouldReceive('isValidVATNumber')
            ->andThrow( new VATCheckUnavailableException() );
        $validator = new VatCalculatorValidatorExtension(
            $this->translator,
            $this->data,
            $this->rules,
            $this->messages
        );
        $this->assertTrue($validator->fails());
    }

    public function testDefaultErrorMessageWorks()
    {
        $vatNumber     = "098 891";
        $this->data = array(
            'customer_vat' => $vatNumber
        );
        VatCalculator::shouldReceive('isValidVATNumber')
            ->with( $vatNumber )
            ->andThrow( new VATCheckUnavailableException() );
        $validator = new VatCalculatorValidatorExtension(
            $this->translator,
            $this->data,
            $this->rules,
            $this->messages
        );
        $custom_messages = $validator->getCustomMessages();

        $this->assertArrayHasKey('vat_number', $custom_messages);
        $this->assertEquals($custom_messages['vat_number'], $this->testDefaultErrorMessage);

        $errors = $validator->messages();
        $this->assertTrue(is_object($errors), 'Asserting that $validator->messages() returns an object.');
        $this->assertInstanceOf('Illuminate\Support\MessageBag', $errors);
        $errors = $errors->toArray();
        $this->assertArrayHasKey('customer_vat', $errors);
        $this->assertNotEmpty($errors['customer_vat']);
        $this->assertEquals('This is a test error message for customer vat.', $errors['customer_vat'][0]);
    }

    public function testErrorMessageOverrideWorks()
    {
        $test_message = 'This is a test override message for :attribute.';

        $vatNumber     = "098 891";
        $this->data = array(
            'customer_vat' => $vatNumber
        );
        VatCalculator::shouldReceive('isValidVATNumber')
            ->with( $vatNumber )
            ->andThrow( new VATCheckUnavailableException() );
        $validator = new VatCalculatorValidatorExtension(
            $this->translator,
            $this->data,
            $this->rules,
            ['vat_number' => $test_message]
        );
        $custom_messages = $validator->getCustomMessages();

        $this->assertArrayHasKey('vat_number', $custom_messages);
        $this->assertEquals($custom_messages['vat_number'], $test_message);

        $errors = $validator->messages();
        $this->assertTrue(is_object($errors), 'Asserting that $validator->messages() returns an object.');
        $this->assertInstanceOf('Illuminate\Support\MessageBag', $errors);
        $errors = $errors->toArray();
        $this->assertArrayHasKey('customer_vat', $errors);
        $this->assertNotEmpty($errors['customer_vat']);
        $this->assertEquals('This is a test override message for customer vat.', $errors['customer_vat'][0]);

    }
}
