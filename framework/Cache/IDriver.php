<?php

namespace T4\Cache;

use T4\Core\Config;

/**
 * Interface IDriver
 * @package T4\Cache
 */
interface IDriver
{

    public function __construct(Config $config);

    public function cache($key, callable $callback, $time = null);

    public function __invoke($key, callable $callback, $time = null);

}