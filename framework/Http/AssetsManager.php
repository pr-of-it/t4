<?php

namespace T4\Http;

use T4\Core\Exception;
use T4\Core\TSingleton;
use T4\Fs\Helpers;

class AssetsManager
{

    use TSingleton;

    protected $assets = [];

    protected $publishCss = [];
    protected $publishJs = [];

    /**
     * Публикует ресурс (файл или директорию)
     * Возвращает публичный URL ресурса
     * @param string $path
     * @return string
     */
    public function publish($path)
    {
        // Получаем абсолютный путь в ФС до ресурса и узнаем тип ресурса
        $realPath = $this->getRealPath($path);
        $type = is_dir($realPath) ? 'dir' : 'file';

        // Ищем такой путь среди уже опубликованных при этом запуске приложения
        foreach ($this->assets as $asset) {
            if ( $asset['path'] == $realPath || false !== strpos($realPath, $asset['path']) ) {
                return str_replace(DS, '/', str_replace($asset['path'], $asset['url'], $realPath));
            }
        }

        // Не нашли, нужно публиковать ресурс
        if ($type == 'dir') {
            $baseRealPath = $realPath;
        } else {
            $baseRealPath = pathinfo($realPath, PATHINFO_DIRNAME);
        }
        $pathHash = substr(md5($baseRealPath), 0, 12);
        $assetBasePath = ROOT_PATH_PUBLIC . DS . 'Assets' . DS . $pathHash;
        $assetBaseUrl = '/Assets/' . $pathHash;

        if ( !is_readable($assetBasePath) ) {
            Helpers::mkDir($assetBasePath);
            if ('dir' == $type) {
                Helpers::copyDir($realPath, $assetBasePath);
            } else {
                Helpers::copyFile($realPath, $assetBasePath);
            }
        } else {
            if ('dir' == $type && filemtime($realPath.DS.'.') > filemtime($assetBasePath.DS.'.')) {
                Helpers::copyDir($realPath, $assetBasePath);
            } elseif (filemtime($realPath) > filemtime($assetBasePath)) {
                Helpers::copyFile($realPath, $assetBasePath);
            }
        }

        $asset = &$this->assets[];
        $asset['path'] = $realPath;
        $asset['url'] = str_replace(DS, '/', str_replace($baseRealPath, $assetBaseUrl, $realPath));

        return $asset['url'];

    }

    /*
     * CSS
     */

    public function registerCss($url)
    {
        $this->publishCss[] = $url;
    }

    public function publishCss($path)
    {
        $url = $this->publish($path);
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

    /*
     * JS
     */

    public function registerJs($url)
    {
        $this->publishJs[] = $url;
    }

    public function publishJs($path)
    {
        $url = $this->publish($path);
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

    /*
     * Магия
     */

    public function __invoke($path)
    {
        return $this->publish($path);
    }

    /**
     * Получает абсолютный путь из условной записи пути до ресурса
     * Обрабатывает две возможности:
     * 1. Путь к ресурсу начинается с // - путь указан относительно корня фреймворка
     * 2. Путь к ресурсу начинается с / - путь указан относительно корня protected
     * Любой другой путь не будет изменен
     * @param $path
     * @return string
     * @throws \T4\Core\Exception
     */
    protected function getRealPath($path)
    {
        if ( preg_match('~^\/\/~', $path) )
            $realPath = preg_replace('~^\/\/~', \T4\ROOT_PATH.DS, $path);
        elseif (  preg_match('~^\/~', $path)  ) {
            $realPath = preg_replace('~^\/~', ROOT_PATH_PROTECTED.DS, $path);
        } else {
            $realPath = $path;
        }
        $realPath = realpath($realPath);
        if (false === $realPath)
            throw new Exception('Path \'' . $path . '\' for asset is not found');

        return $realPath;
    }

}