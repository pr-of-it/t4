<?php

require_once realpath(__DIR__ . '/../../framework/boot.php');

class RouteTest extends PHPUnit_Framework_TestCase
{

    public function testFromString()
    {
        $route = new \T4\Mvc\Route('/mod/ctrl/act');
        $this->assertEquals(
            new \T4\Mvc\Route(['module' => 'Mod', 'controller' => 'Ctrl', 'action' => 'Act', 'params' => []]),
            $route
        );
        $route = new \T4\Mvc\Route('//ctrl/act');
        $this->assertEquals(
            new \T4\Mvc\Route(['module' => '', 'controller' => 'Ctrl', 'action' => 'Act', 'params' => []]),
            $route
        );
        $route = new \T4\Mvc\Route('/mod//act');
        $this->assertEquals(
            new \T4\Mvc\Route(['module' => 'Mod', 'controller' => 'Index', 'action' => 'Act', 'params' => []]),
            $route
        );
        $route = new \T4\Mvc\Route('/mod/ctrl/');
        $this->assertEquals(
            new \T4\Mvc\Route(['module' => 'Mod', 'controller' => 'Ctrl', 'action' => 'Default', 'params' => []]),
            $route
        );
        $route = new \T4\Mvc\Route('///act');
        $this->assertEquals(
            new \T4\Mvc\Route(['module' => '', 'controller' => 'Index', 'action' => 'Act', 'params' => []]),
            $route
        );

        $route = new \T4\Mvc\Route('///');
        $this->assertEquals(
            new \T4\Mvc\Route(['module' => '', 'controller' => \T4\Mvc\Router::DEFAULT_CONTROLLER, 'action' => \T4\Mvc\Router::DEFAULT_ACTION, 'params' => []]),
            $route
        );
        $route = new \T4\Mvc\Route('///', true);
        $this->assertEquals(
            new \T4\Mvc\Route(['module' => '', 'controller' => '', 'action' => '', 'params' => []]),
            $route
        );
        $route = new \T4\Mvc\Route('///', false);
        $this->assertEquals(
            new \T4\Mvc\Route(['module' => '', 'controller' => \T4\Mvc\Router::DEFAULT_CONTROLLER, 'action' => \T4\Mvc\Router::DEFAULT_ACTION, 'params' => []]),
            $route
        );

        $route = new \T4\Mvc\Route('/mod/ctrl/act(a=1)');
        $this->assertEquals(
            new \T4\Mvc\Route(['module' => 'Mod', 'controller' => 'Ctrl', 'action' => 'Act', 'params' => ['a' => 1]]),
            $route
        );
        $route = new \T4\Mvc\Route('/mod/ctrl/act(a=1,b=2)');
        $this->assertEquals(
            new \T4\Mvc\Route(['module' => 'Mod', 'controller' => 'Ctrl', 'action' => 'Act', 'params' => ['a' => 1, 'b' => 2]]),
            $route
        );
        $route = new \T4\Mvc\Route('//ctrl/act(a=1,b=2)');
        $this->assertEquals(
            new \T4\Mvc\Route(['module' => '', 'controller' => 'Ctrl', 'action' => 'Act', 'params' => ['a' => 1, 'b' => 2]]),
            $route
        );
        $route = new \T4\Mvc\Route('/mod//act(a=1,b=2)');
        $this->assertEquals(
            new \T4\Mvc\Route(['module' => 'Mod', 'controller' => 'Index', 'action' => 'Act', 'params' => ['a' => 1, 'b' => 2]]),
            $route
        );
        $route = new \T4\Mvc\Route('/mod/ctrl/(a=1,b=2)');
        $this->assertEquals(
            new \T4\Mvc\Route(['module' => 'Mod', 'controller' => 'Ctrl', 'action' => 'Default', 'params' => ['a' => 1, 'b' => 2]]),
            $route
        );
        $route = new \T4\Mvc\Route('///act(a=1,b=2)');
        $this->assertEquals(
            new \T4\Mvc\Route(['module' => '', 'controller' => 'Index', 'action' => 'Act', 'params' => ['a' => 1, 'b' => 2]]),
            $route
        );
        $route = new \T4\Mvc\Route('///(a=1,b=2)');
        $this->assertEquals(
            new \T4\Mvc\Route(['module' => '', 'controller' => 'Index', 'action' => 'Default', 'params' => ['a' => 1, 'b' => 2]]),
            $route
        );

    }

    public function testMakeString()
    {
        $route = new \T4\Mvc\Route(['module' => '', 'controller' => '', 'action' => '']);
        $this->assertEquals('///', $route->toString());
        $this->assertEquals('///', $route->toString(true));
        $this->assertEquals('//Index/Default', $route->toString(false));

        $route = new \T4\Mvc\Route(['module' => '', 'controller' => 'Index', 'action' => 'Default']);
        $this->assertEquals('///', $route->toString());
        $this->assertEquals('///', $route->toString(true));
        $this->assertEquals('//Index/Default', $route->toString(false));

        $route = new \T4\Mvc\Route(['module' => 'Shop', 'controller' => 'Goods', 'action' => 'All']);
        $this->assertEquals('/Shop/Goods/All', $route->toString());
        $this->assertEquals('/Shop/Goods/All', $route->toString(true));
        $this->assertEquals('/Shop/Goods/All', $route->toString(false));
    }

}
 