<?php

require_once realpath(__DIR__ . '/../../framework/boot.php');

class RouteTest extends PHPUnit_Framework_TestCase
{

    public function testFromString()
    {
        $route = new \T4\Mvc\Route('///');
        $this->assertEquals(['module'=>'', 'controller'=>'', 'action'=>'', 'params'=>[]], $route->toArray());
        $route = new \T4\Mvc\Route('///', false);
        $this->assertEquals(['module'=>'', 'controller'=>\T4\Mvc\Router::DEFAULT_CONTROLLER, 'action'=>\T4\Mvc\Router::DEFAULT_ACTION, 'params'=>[]], $route->toArray());

        $route = new \T4\Mvc\Route('/Shop/Goods/All(sort=price)');
        $this->assertEquals(['module'=>'Shop', 'controller'=>'Goods', 'action'=>'All', 'params'=>['sort'=>'price']], $route->toArray());
    }

    public function testMakeString()
    {
        $route = new \T4\Mvc\Route(['module'=>'', 'controller'=>'', 'action'=>'']);
        $this->assertEquals('///', $route->makeString());
        $this->assertEquals('///', $route->makeString(true));
        $this->assertEquals('//Index/Default', $route->makeString(false));

        $route = new \T4\Mvc\Route(['module'=>'', 'controller'=>'Index', 'action'=>'Default']);
        $this->assertEquals('///', $route->makeString());
        $this->assertEquals('///', $route->makeString(true));
        $this->assertEquals('//Index/Default', $route->makeString(false));

        $route = new \T4\Mvc\Route(['module'=>'Shop', 'controller'=>'Goods', 'action'=>'All']);
        $this->assertEquals('/Shop/Goods/All', $route->makeString());
        $this->assertEquals('/Shop/Goods/All', $route->makeString(true));
        $this->assertEquals('/Shop/Goods/All', $route->makeString(false));
    }

}
 