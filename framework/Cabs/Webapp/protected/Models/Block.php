<?php

namespace App\Models;

use T4\Mvc\Application;
use T4\Mvc\Router;
use T4\Orm\Model;

class Block
    extends Model
{
    public static $schema = [
        'table' => '__blocks',
        'columns' => [
            'section'   => ['type'=>'int'],
            'path'      => ['type'=>'string'],
            'template'  => ['type'=>'string'],
            'options'   => ['type'=>'text', 'default'=>'{}'],
            'order'     => ['type'=>'int', 'default'=>0],
        ],
    ];

    public function getAllTemplates()
    {
        $route = Router::getInstance()->splitInternalPath($this->path);
        $controller = Application::getInstance()->createController($route->module, $route->controller);
        $templates = [];
        foreach ($controller->getTemplatePaths() as $path) {
            foreach (glob($path . DS . $route->action . '.*.block.html') as $filename) {
                preg_match('~.*\.([^\.]+)\.block\.html~', basename($filename), $m);
                $templates[] = $m[1];
            }
        }
        return $templates;
    }
}