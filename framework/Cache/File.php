<?php

namespace T4\Cache;

use T4\Core\Config;
use T4\Fs\Helpers;

class File
    implements IDriver
{

    const DEFAULT_CACHE_TIME = 300;

    protected $config;

    public function __construct(Config $config)
    {
        if (empty($config->path)) {
            throw new Exception('Invalid cache files path');
        }
        $this->config = $config;
    }

    public function cache($key, callable $callback, $time = null)
    {
        if (null === $time) {
            $time = self::DEFAULT_CACHE_TIME;
        }

        $cachePath = $this->config->path;
        if (!is_readable($cachePath)) {
            Helpers::mkDir($cachePath);
        }

        $fileName = $cachePath . DS . md5($key) . '.cache';
        clearstatcache(true, $fileName);

        if (!file_exists($fileName) || (time() - filemtime($fileName) > (int)$time)) {
            $res = call_user_func($callback);
            file_put_contents($fileName, serialize($res));
            return $res;
        } else {
            return unserialize(file_get_contents($fileName));
        }
    }

    public function __invoke($key, callable $callback, $time = null)
    {
        return $this->cache($key, $callback, $time);
    }

}