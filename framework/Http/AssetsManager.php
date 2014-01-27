<?php

namespace T4\Http;

use T4\Core\TSingleton;

class AssetsManager
{

    use TSingleton;

    protected $assets = [];

    protected $publishCss = [];
    protected $publishJs = [];

    public function __invoke($path)
    {
        if (!isset($this->assets[$path])) {
            $this->assets[$path]['path'] = $this->makeRealPath($path);
            $this->assets[$path]['url'] = $this->makeUrl($this->assets[$path]['path']);
        }
        return $this->assets[$path]['url'];
    }

    public function registerCss($url)
    {
        $this->publishCss[] = $url;
    }

    public function publishCss($path)
    {
        $url = $this($path);
        $this->registerCss($url);
        return $url;
    }

    public function getPublishedCss()
    {
        $links = [];
        foreach ($this->publishCss as $css)
            $links[] = '<link rel="stylesheet" href="' . $css . '">';
        return implode("\n", $links)."\n";
    }

    public function registerJs($url)
    {
        $this->publishCss[] = $url;
    }

    public function publishJs($path)
    {
        $url = $this($path);
        $this->registerJs($url);
        return $url;
    }

    public function getPublishedJs()
    {
        $links = [];
        foreach ($this->publishJs as $js)
            $links[] = '<script type="text/javascript" src="' . $js . '"></script>';
        return implode("\n", $links)."\n";
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
        // TODO: переписать через FS\Helpers
        if (!is_readable(dirname($assetPath)))
            mkdir(dirname($assetPath), 0777, true);
        copy($realPath, $assetPath);
        touch($assetPath);
    }

}