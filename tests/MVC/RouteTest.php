<?php

require_once realpath(__DIR__ . '/../../framework/boot.php');

class RouteTest extends PHPUnit_Framework_TestCase
{

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
 