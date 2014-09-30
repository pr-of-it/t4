<?php

namespace T4\Cache;

abstract class ACache
{
    const DEFAULT_CACHE_TIME = 300;

    /**
     * @param string $key
     * @param callable $callback
     * @param int $time
     * @return mixed
     */
    abstract public function __invoke($key, $callback, $time=self::DEFAULT_CACHE_TIME);

}