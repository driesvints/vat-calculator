<?php


namespace Tests\Util;


use Mpociot\VatCalculator\Util\ConfigWrapper;

class ConfigWrapperTest extends \PHPUnit\Framework\TestCase
{
    public function testGetMethodShouldReturnValue()
    {
        $config = ['dummyKey' => 'dummyValue'];

        $configWrapper = new ConfigWrapper($config);
        $value = $configWrapper->get('dummyKey');
        $this->assertEquals($config['dummyKey'], $value);
    }

    public function testGetMethodShouldReturnDefaultValue()
    {
        $config = ['dummyKey' => 'dummyValue'];
        $configWrapper = new ConfigWrapper($config);

        $defaultValue = $configWrapper->get('testKey', 'defaultValue');

        $this->assertEquals('defaultValue', $defaultValue);
    }

    public function testHasReturnsBoolean()
    {
        $config = ['dummyKey' => 'dummyValue'];
        $configWrapper = new ConfigWrapper($config);
        $this->assertTrue($configWrapper->has('dummyKey'));
        $this->assertFalse($configWrapper->has('testKey'));
    }


}