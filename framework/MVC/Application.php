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
    public $db;

    public $assets;

    private function __construct()
    {
        $this->assets = AssetsManager::getInstance();
        $this->config = new Config($this->path . DS . 'config.php');
        try {
            $this->db = new Std;
            foreach ($this->config->db as $connection => $connectionConfig) {
                $this->db[$connection] = new Connection($connectionConfig);
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
                        $this->getPath() . DS . 'templates' . DS . $route['controller'],
                        $this->getPath() . DS . 'layouts'
                    ]);
                    $stream = $view->render($route['action'] . '.' . $route['format'], ['this' => $controller] + (array)$controller->getData());
                    echo $stream;
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