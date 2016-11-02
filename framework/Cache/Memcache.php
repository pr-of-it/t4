<?php

namespace T4\Cache;

use T4\Core\Config;

class Memcache
    implements IDriver
{

    const DEFAULT_PORT = 11211;
    const DEFAULT_CACHE_TIME = 300;

    protected $config;
    protected $memcache;

    public function __construct(Config $config)
    {
        if (empty($config->host)) {
            throw new Exception('Invalid memcached host');
        }
        if (empty($config->port)) {
            $config->port = self::DEFAULT_PORT;
        }
        $this->config = $config;
        try {
            $this->memcache = new \Memcache();
            $this->memcache->addserver($config->host, $config->port);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function cache($key, callable $callback, $time = null)
    {
        if (null === $time) {
            $time = self::DEFAULT_CACHE_TIME;
        }

        if (false === $value = $this->memcache->get($key)) {
            $res = call_user_func($callback);
            $this->memcache->set($key, $res, 0, $time);
            return $res;
        } else {
            return $value;
        }
    }

    public function __invoke($key, callable $callback, $time = null)
    {
        return $this->cache($key, $callback, $time);
    }

}