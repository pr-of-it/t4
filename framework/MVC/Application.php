<?php

namespace T4\MVC;

use T4\Core\TSingleton;
use T4\Core\Config;
use T4\Core\Exception;

class Application
{
    use TSingleton;

    protected $path = \ROOT_PATH;

    public function run()
    {

        try {

            $route = Router::getInstance()->parseUrl($_GET['__path']);

            $controllerClass = '\\App\\Controllers\\'.$route['controller'];
            $controller = new $controllerClass;
            $controller->action($route['action']);
            $view = new View([
                $this->getPath().DS.'templates'.DS.$route['controller'],
                $this->getPath().DS.'layouts'
            ]);
            $stream = $view->render($route['action'].'.html', $controller->data);

            echo $stream;

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