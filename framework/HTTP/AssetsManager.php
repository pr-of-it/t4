<?php

namespace T4\HTTP;


use T4\Core\Log;
use T4\Core\TSingleton;

class AssetsManager
{

    use TSingleton;

    protected $assets = [];

    public function __invoke($path)
    {
        if (!isset($this->assets[$path])) {
            $this->assets[$path]['path'] = $this->makeRealPath($path);
            $this->assets[$path]['url'] = $this->makeUrl($this->assets[$path]['path']);
        }
        return $this->assets[$path]['url'];
    }

    protected function makeRealPath($path)
    {
        return realpath(preg_replace(['/^\/\//', '/^\//'], [\T4\ROOT_PATH . DS, ROOT_PATH_PROTECTED . DS], $path));
    }

    protected function makeUrl($path)
    {
        $baseName = basename($path);
        $basePath = pathinfo($path, PATHINFO_DIRNAME);
        $basePathHash = substr(md5($basePath), 0, 12);
        $assetPath = ROOT_PATH_PUBLIC . DS . 'Assets' . DS . $basePathHash;
        $assetUrl = '/Assets/' . $basePathHash;
        if (!$this->checkCopy($path, $assetPath . DS . $baseName)) {
            $this->makeCopy($path, $assetPath . DS . $baseName);
        }
        return $assetUrl . '/' . $baseName;
    }

    protected function checkCopy($realPath, $assetPath)
    {
        if (!is_readable(dirname($assetPath))) {
            return false;
        }
        if (!is_readable($assetPath)) {
            return false;
        }
        if (filemtime($realPath) >= filemtime($assetPath)) {
            return false;
        }
        return true;
    }

    protected function makeCopy($realPath, $assetPath)
    {
        if (!is_readable(dirname($assetPath)))
            mkdir(dirname($assetPath), 0777, true);
        copy($realPath, $assetPath);
        touch($assetPath);
    }

}