<?php

namespace T4\MVC;

use T4\Core\Std;
use T4\Core\TSingleton;
use T4\Core\Config;
use T4\Core\Exception;
use T4\Dbal\Connection;
use T4\HTTP\AssetsManager;

class Application
{
    use TSingleton;

    public $path = \ROOT_PATH_PROTECTED;

    public $config;

    /**
     * @var Std
     */
    public $db;

    public $assets;

    private function __construct()
    {
        $this->assets = AssetsManager::getInstance();
        $this->config = new Config($this->path . DS . 'config.php');
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
            $controllerClass = '\\App\\Controllers\\' . $route['controller'];
            $controller = new $controllerClass;
            $controller->action($route['action']);

            switch ($route['format']) {
                case 'html':
                    $view = new View([
                        $this->getPath() . DS . 'Templates' . DS . $route['controller'],
                        $this->getPath() . DS . 'Layouts'
                    ]);
                    $view->display($route['action'] . '.' . $route['format'], ['this' => $controller] + (array)$controller->getData());
                    break;
                case 'json':
                    header('Content-Type: application/json');
                    echo json_encode($controller->getData());
                    die;
                default:
                    $view = new View([
                        $this->getPath() . DS . 'Templates' . DS . $route['controller'],
                    ]);
                    $view->display($route['action'] . '.' . $route['format'], ['this' => $controller] + (array)$controller->getData());
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
     * @return Config Объект конфига роутинга
     */
    public function getRouteConfig()
    {
        return new Config($this->getPath() . DS . 'routes.php');
    }

}