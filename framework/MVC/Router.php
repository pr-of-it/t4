<?php

namespace T4\MVC;
use T4\Core\TSingleton;

class Router {
    use TSingleton;

    public function getRoute($path) {
        return [
            'controller' => 'index',
            'action' => 'default'
        ];
    }

} 