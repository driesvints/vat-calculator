<?php namespace Mpociot\VatCalculator;

use Mockery as m;
use PHPUnit_Framework_TestCase;


function config_path($path)
{
    return 'test/' . $path;
}

class VatCalculatorServiceProviderTest extends PHPUnit_Framework_TestCase
{
    /**
     * Calls Mockery::close
     */
    public function tearDown()
    {
        m::close();
    }


    public function testShouldBoot()
    {
        $test = $this;
        $app = [];
        $app['validator'] = m::mock('Validator');
        $app['validator']->shouldReceive('addNamespace');
        $app['validator']->shouldReceive('resolver');
        $sp = m::mock('Mpociot\VatCalculator\VatCalculatorServiceProvider[publishes,loadTranslationsFrom]',
            [$app]
        );
        $sp->shouldAllowMockingProtectedMethods();

        $sp->shouldReceive('publishes')
            ->with(m::type('array'))
            ->once()
            ->andReturnUsing(function ($array) use ($test) {
                $test->assertContains('test/vat_calculator.php', $array);
            });


        $sp->shouldReceive('loadTranslationsFrom')
            ->with(m::type('string'), 'vatnumber-validator')
            ->once()
            ->andReturnUsing(function ($a, $b) use ($test) {
                $test->assertStringEndsWith('lang', $a);
            });
        $sp->boot();
    }

    public function testShouldRegister()
    {
        $sp = m::mock('Mpociot\VatCalculator\VatCalculatorServiceProvider[mergeConfig,registerVatCalculator,registerFacade]',
            ['something']
        );
        $sp->shouldAllowMockingProtectedMethods();
        $sp->shouldReceive( 'registerVatCalculator',
            'registerFacade',
            'mergeConfig')->once();

        $sp->register();
    }

    public function testShouldRegisterVatCalculator()
    {
        $test = $this;
        $app = m::mock('App');
        $sp = m::mock('Mpociot\VatCalculator\VatCalculatorServiceProvider',
            [$app]
        );

        $app->shouldReceive('bind')
            ->once()->andReturnUsing(
            // Make sure that the name is 'confide.repository'
            // and that the closure passed returns the correct
            // kind of object.
                function ($name, $closure) use ($test, $app) {
                    $test->assertEquals('vatcalculator', $name);
                    $test->assertInstanceOf(
                        'Mpociot\VatCalculator\Facades\VatCalculator',
                        $closure($app)
                    );
                }
            );

        $sp->registerVatCalculator();
    }

}