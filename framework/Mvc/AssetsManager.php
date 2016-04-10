<?php

namespace T4\Mvc;

use T4\Core\ISingleton;
use T4\Core\Std;
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
    implements ISingleton
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

        // TODO: смущает меня этот кусок, если честно. Надо внимательно его перепроверить.
        foreach ($this->assets as $asset) {
            if (false !== strpos($realPath, $asset['path'])) {
                return str_replace(DS, '/', str_replace($asset['path'], $asset['url'], $realPath));
            }
        }

        $type = is_dir($realPath) ? 'dir' : 'file';


        // Получаем время последней модификации ресурса
        // и, заодно, путь до него и до возможной публикации
        clearstatcache(true, $realPath);
        if ('dir' == $type) {
            $baseRealPath = $realPath;
            $lastModifiedTime = Helpers::dirMTime($realPath . DS . '.');
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

        } else {

            // Вариант 2 - нужный путь уже есть, нужно проверить наличие там нашего файла
            clearstatcache();
            if ('file' == $type) {
                // Файл не найден, копируем его
                // Файл найден, но протух - перезаписываем
                if (
                    !is_readable($assetBasePath . DS . $baseRealName)
                    || $lastModifiedTime >= filemtime($assetBasePath . DS . $baseRealName)
                ) {
                    Helpers::copyFile($realPath, $assetBasePath);
                }
            } else {
                // Это папка. Она уже скопирована. Но протухла
                if ($lastModifiedTime >= filemtime($assetBasePath . DS . '.')) {
                    Helpers::copyDir($realPath, $assetBasePath);
                }
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

    public function registerCssUrl($url)
    {
        $this->publishedCss[] = new Std([
            'type' => 'link',
            'url' => $url,
        ]);
    }

    public function registerCss($text)
    {
        $this->publishedCss[] = new Std([
            'type' => 'text',
            'text' => $text,
        ]);
    }

    public function publishCssFile($path)
    {
        $url = $this->publish($path);
        $this->registerCssUrl($url);
        return $url;
    }

    public function getPublishedCss()
    {
        $links = [];
        foreach ($this->publishedCss as $css) {
            if ($css->type == 'link') {
                $links[] = '<link rel="stylesheet" href="' . $css->url . '">';
            } elseif ($css->type == 'text') {
                $links[] = '<style type="text/css">' . "\n" . $css->text . "\n" . '</style>';
            }
        }
        return implode("\n", $links) . "\n";
    }

    /*
     * JS
     */

    public function registerJsUrl($url)
    {
        $this->publishedJs[] = new Std([
            'type' => 'link',
            'url' => $url,
        ]);
    }

    public function registerJs($text)
    {
        $this->publishedJs[] = new Std([
            'type' => 'text',
            'text' => $text,
        ]);
    }

    public function publishJsFile($path)
    {
        $url = $this->publish($path);
        $this->registerJsUrl($url);
        return $url;
    }

    public function getPublishedJs()
    {
        $links = [];
        foreach ($this->publishedJs as $js)
            if ($js->type == 'link') {
                $links[] = '<script type="text/javascript" src="' . $js->url . '"></script>';
            } elseif ($js->type == 'text') {
                $links[] = '<script type="text/javascript">' . "\n" . $js->text . "\n" . '</script>';
            }
        return implode("\n", $links) . "\n";
    }

    /**
     * TODO: заменить на Helpers::getRealPath()
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
        if (0 === strpos($path, '//')) {
            $realPath = \T4\ROOT_PATH . DS . substr($path, 2);
        } elseif (0 === strpos($path, '/')) {
            $realPath = ROOT_PATH_PROTECTED . DS . substr($path, 1);
        } else {
            throw new Exception('Path \'' . $path . '\' for asset is invalid');
        }
        $realPath = realpath($realPath);
        if (false === $realPath)
            throw new Exception('Path \'' . $path . '\' for asset is not found');

        return $realPath;

    }

}