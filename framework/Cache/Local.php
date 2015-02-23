<?php

namespace T4\Cache;

use T4\Core\Config;
use T4\Fs\Helpers;

class Local
    extends ACache
{

    protected $path;

    public function __construct(Config $config = null)
    {
        $this->path = ROOT_PATH_PROTECTED . DS . 'Cache';
        if (null !== $config) {
            if (!empty($config->path)) {
                $this->path = $config->path;
            }
        }
    }

    public function __invoke($key, $callback, $time = self::DEFAULT_CACHE_TIME)
    {
        $cachePath = $this->path;
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

}