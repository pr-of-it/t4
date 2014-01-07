<?php

namespace T4\MVC;

use T4\Core\TSingleton;
use T4\Core\Config;
use T4\Core\Exception;
use T4\HTTP\AssetsManager;

class Application
{
    use TSingleton;

    public $path = \ROOT_PATH_PROTECTED;

    public $assets;

    private function __construct() {
        $this->assets = new AssetsManager();
    }

    public function run()
    {

        try {

            $route = Router::getInstance()->parseUrl($_GET['__path']);

            $controllerClass = '\\App\\Controllers\\'.$route['controller'];
            $controller = new $controllerClass;
            $controller->action($route['action']);

            switch ($route['format']) {
                case 'html':
                    $view = new View([
                        $this->getPath().DS.'templates'.DS.$route['controller'],
                        $this->getPath().DS.'layouts'
                    ]);
                    $stream = $view->render($route['action'].'.'.$route['format'], ['this'=>$controller]+(array)$controller->getData());
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