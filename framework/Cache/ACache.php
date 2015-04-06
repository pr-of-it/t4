<?php

namespace T4\Cache;

use T4\Core\Config;

abstract class ACache
{
    const DEFAULT_CACHE_TIME = 300;

    public function __construct(Config $config = null)
    {
    }

    /**
     * @param string $key
     * @param callable $callback
     * @param int $time
     * @return mixed
     */
    abstract public function __invoke($key, $callback, $time = self::DEFAULT_CACHE_TIME);

}