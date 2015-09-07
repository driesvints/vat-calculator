<?php

use Mockery as m;

class VatCalculatorRoutesTest extends PHPUnit_Framework_TestCase
{
    /**
     * Calls Mockery::close
     */
    public function tearDown()
    {
        m::close();
    }

    public function testShouldRegisterRoutes()
    {
        $app = m::mock('App');
        $sp = m::mock('Mpociot\VatCalculator\VatCalculatorServiceProvider',
            [$app]
        );
        $sp->shouldAllowMockingProtectedMethods()->shouldDeferMissing();

        $config = m::mock('Illuminate\Contracts\Config\Repository');
        $config->shouldReceive('get')
            ->once()
            ->with('vat_calculator.use_routes', true )
            ->andReturn( true );

        $app->shouldReceive('make')
            ->once()
            ->with('Illuminate\Contracts\Config\Repository')
            ->andReturn( $config );

        Route::$assertCalled = true;
        Route::$test = $this;

        $sp->registerRoutes();
    }

    public function testShouldNotRegisterRoutesWhenDefinedInConfig()
    {
        $app = m::mock('App');
        $sp = m::mock('Mpociot\VatCalculator\VatCalculatorServiceProvider',
            [$app]
        );
        $sp->shouldAllowMockingProtectedMethods()->shouldDeferMissing();

        $config = m::mock('Illuminate\Contracts\Config\Repository');
        $config->shouldReceive('get')
            ->once()
            ->with('vat_calculator.use_routes', true )
            ->andReturn( false );

        $app->shouldReceive('make')
            ->once()
            ->with('Illuminate\Contracts\Config\Repository')
            ->andReturn( $config );

        Route::$assertCalled = false;
        Route::$test = $this;

        $sp->registerRoutes();
    }

}

class Route {
    public static $test;
    public static $assertCalled;
    public static function get(){
        self::$test->assertTrue( self::$assertCalled );
    }
}