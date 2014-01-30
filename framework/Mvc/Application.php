<?php

namespace T4\Mvc;

use T4\Core\Config;
use T4\Core\Exception;
use T4\Core\Std;
use T4\Core\TSingleton;
use T4\Dbal\Connection;
use T4\Http\AssetsManager;

class Application
{
    use TSingleton;

    public $path = \ROOT_PATH_PROTECTED;

    /**
     * @var \T4\Core\Config
     */
    public $config;

    /**
     * @var \T4\Core\Std
     */
    public $db;

    /**
     * @var \T4\Http\AssetsManager
     */
    public $assets;

    /**
     * @var \T4\Core\Std
     */
    public $extensions;

    protected function __construct()
    {
        $this->assets = AssetsManager::getInstance();
        $this->config = new Config($this->getPath() . DS . 'config.php');
        try {

            $this->db = new Std;
            foreach ($this->config->db as $connection => $connectionConfig) {
                $this->db->{$connection} = new Connection($connectionConfig);
            }

            $this->extensions = new Std;
            if (isset($this->config->extensions)) {
                foreach ($this->config->extensions as $extension => $options) {
                    $extensionClassName = 'Extensions\\'.ucfirst($extension).'\\Extension';
                    if ( class_exists('\\App\\'.$extensionClassName) ) {
                        $extensionClassName = '\\App\\'.$extensionClassName;
                    } else {
                        $extensionClassName = '\\T4\\'.$extensionClassName;
                    }
                    $this->extensions->{$extension} = new $extensionClassName($options);
                    $this->extensions->{$extension}->setApp($this);
                    $this->extensions->{$extension}->init();
                }
            }

        } catch (\T4\Dbal\Exception $e) {
            echo $e->getMessage();
            die;
        }
    }

    public function run()
    {

        try {

            $route = Router::getInstance()->parseUrl($_GET['__path']);
            $controller = $this->createController($route->controller);
            $controller->action($route->action, $route->params);

            switch ($route->format) {
                case 'json':
                    header('Content-Type: application/json');
                    echo json_encode($controller->getData());
                    die;
                default:
                case 'html':
                    header('Content-Type: text/html; charset=utf-8');
                    $controller->view->display($route->action . '.' . $route->format, $controller->getData());
                    break;
            }

        } catch (Exception $e) {
            echo $e->getMessage();
            die;
        }

    }

    public function getPath()
    {
        return $this->path;
    }

    /**
     * Возвращает конфиг роутинга приложения
     * @return \T4\Core\Config Объект конфига роутинга
     */
    public function getRouteConfig()
    {
        return new Config($this->getPath() . DS . 'routes.php');
    }

    /**
     * Возвращает экземпляр контроллера согласно объекту роутинга
     * @param string $controller
     * @return \T4\Mvc\Controller
     */
    protected function createController($controller)
    {
        $controllerClass = '\\App\\Controllers\\' . $controller;
        $controller = new $controllerClass;
        return $controller;
    }

}