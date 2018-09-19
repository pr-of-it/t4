<?php

namespace T4\Cache;

use T4\Core\Config;

class Memcached implements IDriver
{

    protected const DEFAULT_PORT = 11211;
    protected const DEFAULT_CACHE_TIME = 300;

    protected $config;
    /**
     * @var \Memcached
     */
    protected $memcached;

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
            $this->memcached = new \Memcached();
            $this->memcached->addServer($config->host, $config->port);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function cache($key, callable $callback, $time = null)
    {
        if (null === $time) {
            $time = self::DEFAULT_CACHE_TIME;
        }

        if (false === $value = $this->memcached->get($key)) {
            $res = $callback();
            $this->memcached->set($key, $res, $time);
            return $res;
        }

        return $value;
    }

    public function __invoke($key, callable $callback, $time = null)
    {
        return $this->cache($key, $callback, $time);
    }
}
