<?php

namespace T4\MVC;

use T4\Core\TSingleton;
use T4\Core\Exception;

class Application
{
    use TSingleton;

    protected $path = \ROOT_PATH;

    public function run()
    {

        try {
            $route = Router::getInstance()->getRoute($_GET['__path']);
        } catch (Exception $e) {
            echo $e->getMessage();
            die;
        }

    }

}