<?php

namespace T4\Mvc;

use T4\Core\Collection;
use T4\Core\Config;
use T4\Core\Flash;
use T4\Dbal\Connections;
use T4\Fs\Helpers;
use T4\Http\Request;

/**
 * Application properties lazy loading
 * Trait TApplicationMagic
 * @package T4\Mvc
 * @mixin \T4\Mvc\Application
 */
trait TApplicationMagic
{

    public function setConfig(Config $config = null)
    {
        $this->config = $config ?: new Config([]);

        if (null !== $this->config->getPath() && file_exists(dirname($this->config->getPath()) . DS . 'routes.php')) {
            $this->setRoutes(new Config(dirname($this->config->getPath()) . DS . 'routes.php'));
        } else {
            $this->setRoutes(new Config([]));
        }

        if (null !== $this->config->getPath() && file_exists(dirname($this->config->getPath()) . DS . 'sections.php')) {
            $this->setSections(new Config(dirname($this->config->getPath()) . DS . 'sections.php'));
        } else {
            $this->setSections(new Config([]));
        }

        if (null !== $this->config->getPath() && file_exists(dirname($this->config->getPath()) . DS . 'blocks.php')) {
            $this->setBlocks(new Config(dirname($this->config->getPath()) . DS . 'blocks.php'));
        } else {
            $this->setBlocks(new Config([]));
        }

        return $this;
    }

    public function setRoutes(Config $config = null)
    {
        if (empty($this->config)) {
            $this->setConfig(new Config([]));
        }
        $this->config->routes = $config ?: new Config([]);
        return $this;
    }

    public function getRouter() : IRouter
    {
        /** @var \T4\Mvc\IRouter $class */
        $class = Router::class;
        return $class::instance()->setConfig($this->config->routes);
    }

    public function setSections(Config $config = null)
    {
        if (empty($this->config)) {
            $this->setConfig(new Config([]));
        }
        $this->config->sections = $config ?: new Config([]);
    }

    public function setBlocks(Config $config = null)
    {
        if (empty($this->config)) {
            $this->setConfig(new Config([]));
        }
        $this->config->blocks = $config ?: new Config([]);
    }

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
            $db = new Connections($this->config->db);
        }
        return $db;
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
        return AssetsManager::instance();
    }

    protected function getFlash()
    {
        static $flash = null;
        if (null === $flash)
            $flash = new Flash();
        return $flash;
    }

} 