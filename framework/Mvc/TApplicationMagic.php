<?php

namespace T4\Mvc;

use T4\Core\Collection;
use T4\Core\Config;
use T4\Core\Flash;
use T4\Core\Std;
use T4\Dbal\Connection;
use T4\Fs\Helpers;
use T4\Http\Request;

/**
 * Application properties lazy loading
 * Class TApplicationMagic
 * @package T4\Mvc
 */
trait TApplicationMagic
{

    protected function getModules()
    {
        static $modules = null;
        if (null === $modules) {
            $dirs = Helpers::listDir(ROOT_PATH_PROTECTED . DS . 'Modules');
            $modules = new Collection();
            foreach ($dirs as $dir) {
                $moduleClassName = '\\App\\Modules\\' . basename($dir) . '\\Module';
                if (class_exists($moduleClassName)) {
                    $modules[] = new $moduleClassName;
                }
            }
        }
        return $modules;
    }

    protected function getDb()
    {
        static $db = null;
        if (null === $db) {
            $db = new Std();
            foreach ($this->config->db as $connection => $connectionConfig) {
                $db->{$connection} = new Connection($connectionConfig);
            }
            $this->db = $db;
        }
        return $db;
    }

    protected function getConfig()
    {
        static $config = null;
        if (null == $config) {
            $config = new Config($this->getPath() . DS . 'config.php');
            $config->routes = new Config($this->getPath() . DS . 'routes.php');
            $config->sections = new Config($this->getPath() . DS . 'sections.php');
            $config->blocks = new Config($this->getPath() . DS . 'blocks.php');
        }
        return $config;
    }

    protected function getRequest()
    {
        static $request = null;
        if (null === $request)
            $request = new Request();
        return $request;
    }

    protected function getUser()
    {
        static $user = null;
        if (null === $user) {
            if (class_exists('\\App\Components\Auth\Identity')) {
                $identity = new \App\Components\Auth\Identity();
                $user = $identity->getUser() ?: null;
            } else {
                return null;
            }
        }
        return $user;
    }

    protected function getAssets()
    {
        return AssetsManager::getInstance();
    }

    protected function getFlash()
    {
        static $flash = null;
        if (null === $flash)
            $flash = new Flash();
        return $flash;
    }

} 