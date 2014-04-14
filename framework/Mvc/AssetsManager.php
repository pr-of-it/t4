<?php

namespace T4\Mvc;

use T4\Mvc\AssetException as Exception;
use T4\Core\TSingleton;
use T4\Fs\Helpers;

/**
 * Менеджер публикации ресурсов
 *
 * Class AssetsManager
 * @package T4\Mvc
 */
class AssetsManager
{

    use TSingleton;

    /**
     * Список ресурсов, опубликованных при текущем запуске приложения
     * @var array
     */
    protected $assets = [];

    /**
     * URLs опубликованных файлов стилей
     * @var array
     */
    protected $publishedCss = [];
    /**
     * URLs опубликованных файлов JS
     * @var array
     */
    protected $publishedJs = [];

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

        // Получаем время последней модификации ресурса
        // и, заодно, путь до него и до возможной публикации
        clearstatcache(true, $realPath);
        if ('dir' == $type) {
            $baseRealPath = $realPath;
            $lastModifiedTime = filemtime($realPath . DS . '.');
        } else {
            $baseRealPath = pathinfo($realPath, PATHINFO_DIRNAME);
            $baseRealName = pathinfo($realPath, PATHINFO_BASENAME);
            $lastModifiedTime = filemtime($realPath);
        }

        $pathHash = substr(md5($baseRealPath), 0, 12);
        $assetBasePath = ROOT_PATH_PUBLIC . DS . 'Assets' . DS . $pathHash;
        $assetBaseUrl = '/Assets/' . $pathHash;

        // Вариант 1 - такого пути в папке Assets нет
        if (!is_readable($assetBasePath)) {
            Helpers::mkDir($assetBasePath);
            if ('dir' == $type) {
                Helpers::copyDir($realPath, $assetBasePath);
            } else {
                Helpers::copyFile($realPath, $assetBasePath);
            }
            $url = str_replace(DS, '/', str_replace($baseRealPath, $assetBaseUrl, $realPath));
            return $url;
        }

        // Вариант 2 - нужный путь уже есть, нужно проверить наличие там нашего файла
        clearstatcache();
        if ('file' == $type) {
            // Файл не найден, копируем его
            // Файл найден, но протух - перезаписываем
            if (
                !is_readable($assetBasePath . DS. $baseRealName)
                || $lastModifiedTime >= filemtime($assetBasePath . DS. $baseRealName)
            ) {
                Helpers::copyFile($realPath, $assetBasePath);
            }
        } else {
            // Это папка. Она уже скопирована. Но протухла
            if ($lastModifiedTime >= filemtime($assetBasePath . DS. '.')) {
                Helpers::copyDir($realPath, $assetBasePath);
            }
        }

        $url = str_replace(DS, '/', str_replace($baseRealPath, $assetBaseUrl, $realPath));
        return $url;

        /*
                $asset = & $this->assets[];
                $asset['path'] = $realPath;
                $asset['url'] = str_replace(DS, '/', str_replace($baseRealPath, $assetBaseUrl, $realPath));

                return $asset['url'];
        */
    }

    /*
     * CSS
     */

    public function registerCss($url)
    {
        $this->publishedCss[] = $url;
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
        foreach ($this->publishedCss as $css)
            $links[] = '<link rel="stylesheet" href="' . $css . '">';
        return implode("\n", $links) . "\n";
    }

    /*
     * JS
     */

    public function registerJs($url)
    {
        $this->publishedJs[] = $url;
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
        foreach ($this->publishedJs as $js)
            $links[] = '<script type="text/javascript" src="' . $js . '"></script>';
        return implode("\n", $links) . "\n";
    }

    /**
     * Получает абсолютный путь из условной записи пути до ресурса
     * Обрабатывает две возможности:
     * 1. Путь к ресурсу начинается с // - путь указан относительно корня фреймворка
     * 2. Путь к ресурсу начинается с / - путь указан относительно корня protected
     * Любой другой путь не разрешен
     * @param $path
     * @return string
     * @throws \T4\Mvc\AssetException
     */
    protected function getRealPath($path)
    {
        if (preg_match('~^\/\/~', $path))
            $realPath = preg_replace('~^\/\/~', \T4\ROOT_PATH . DS, $path);
        elseif (preg_match('~^\/~', $path)) {
            $realPath = preg_replace('~^\/~', ROOT_PATH_PROTECTED . DS, $path);
        } else {
            throw new Exception('Path \'' . $path . '\' for asset is invalid');
        }
        $realPath = realpath($realPath);
        if (false === $realPath)
            throw new Exception('Path \'' . $path . '\' for asset is not found');

        return $realPath;

    }

}