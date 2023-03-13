<?php


namespace Mpociot\VatCalculator\Util;

class ConfigWrapper
{
    /**
     * @var array
     */
    private $wrappedConfig;

    /**
     * @param array $config
     */
    public function __construct(array $config) {
        $this->wrappedConfig= $config;
    }

    public function get(string $key, $defaultValue=null)
    {
        return $this->wrappedConfig[$key]??$defaultValue;
    }

    public function has(string $key): bool
    {
        return isset($this->wrappedConfig[$key]);
    }
}

