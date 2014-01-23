<?php

namespace T4\MVC;

use T4\Core\Config;
use T4\Core\Exception;
use T4\Core\Std;
use T4\Core\TSingleton;
use T4\Dbal\Connection;
use T4\HTTP\AssetsManager;

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
     * @var \T4\HTTP\AssetsManager
     */
    public $assets;

    protected function __construct()
    {
        $this->assets = AssetsManager::getInstance();
        $this->config = new Config($this->getPath() . DS . 'config.php');
        try {
            $this->db = new Std;
            foreach ($this->config->db as $connection => $connectionConfig) {
                $this->db->{$connection} = new Connection($connectionConfig);
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
            $controller = $this->getController($route->controller);
            $controller->action($route->action, $route->params);

            switch ($route->format) {
                case 'json':
                    header('Content-Type: application/json');
                    echo json_encode($controller->getData());
                    die;
                default:
                case 'html':
                    $view = new View([
                        $this->getPath() . DS . 'Templates' . DS . $route->controller,
                        $this->getPath() . DS . 'Layouts'
                    ]);
                    header('Content-Type: text/html; charset=utf-8');
                    $view->display($route->action . '.' . $route->format, $controller->getData());
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
     * @return \T4\MVC\Controller
     */
    protected function getController($controller)
    {
        $controllerClass = '\\App\\Controllers\\' . $controller;
        $controller = new $controllerClass;
        return $controller;
    }

}