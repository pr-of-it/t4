<?php

require_once realpath(__DIR__ . '/../../framework/boot.php');


class RouterTest extends PHPUnit_Framework_TestCase
{

    public function testSplitInternalPath()
    {
        $router = \T4\MVC\Router::getInstance();
        $reflector = new ReflectionMethod($router, 'splitInternalPath');
        $reflector->setAccessible(true);

        $url = '/mod/ctrl/act';
        $this->assertEquals(
            new \T4\MVC\Route(['module' => 'mod', 'controller' => 'ctrl', 'action' => 'act', 'params' => []]),
            $reflector->invoke($router, $url)
        );
        $url = '//ctrl/act';
        $this->assertEquals(
            new \T4\MVC\Route(['module' => '', 'controller' => 'ctrl', 'action' => 'act', 'params' => []]),
            $reflector->invoke($router, $url)
        );
        $url = '/mod//act';
        $this->assertEquals(
            new \T4\MVC\Route(['module' => 'mod', 'controller' => 'Index', 'action' => 'act', 'params' => []]),
            $reflector->invoke($router, $url)
        );
        $url = '/mod/ctrl/';
        $this->assertEquals(
            new \T4\MVC\Route(['module' => 'mod', 'controller' => 'ctrl', 'action' => 'default', 'params' => []]),
            $reflector->invoke($router, $url)
        );
        $url = '///act';
        $this->assertEquals(
            new \T4\MVC\Route(['module' => '', 'controller' => 'Index', 'action' => 'act', 'params' => []]),
            $reflector->invoke($router, $url)
        );
        $url = '///';
        $this->assertEquals(
            new \T4\MVC\Route(['module' => '', 'controller' => 'Index', 'action' => 'default', 'params' => []]),
            $reflector->invoke($router, $url)
        );

        $url = '/mod/ctrl/act(a=1)';
        $this->assertEquals(
            new \T4\MVC\Route(['module' => 'mod', 'controller' => 'ctrl', 'action' => 'act', 'params' => ['a' => 1]]),
            $reflector->invoke($router, $url)
        );
        $url = '/mod/ctrl/act(a=1,b=2)';
        $this->assertEquals(
            new \T4\MVC\Route(['module' => 'mod', 'controller' => 'ctrl', 'action' => 'act', 'params' => ['a' => 1, 'b' => 2]]),
            $reflector->invoke($router, $url)
        );
        $url = '//ctrl/act(a=1,b=2)';
        $this->assertEquals(
            new \T4\MVC\Route(['module' => '', 'controller' => 'ctrl', 'action' => 'act', 'params' => ['a' => 1, 'b' => 2]]),
            $reflector->invoke($router, $url)
        );
        $url = '/mod//act(a=1,b=2)';
        $this->assertEquals(
            new \T4\MVC\Route(['module' => 'mod', 'controller' => 'Index', 'action' => 'act', 'params' => ['a' => 1, 'b' => 2]]),
            $reflector->invoke($router, $url)
        );
        $url = '/mod/ctrl/(a=1,b=2)';
        $this->assertEquals(
            new \T4\MVC\Route(['module' => 'mod', 'controller' => 'ctrl', 'action' => 'default', 'params' => ['a' => 1, 'b' => 2]]),
            $reflector->invoke($router, $url)
        );
        $url = '///act(a=1,b=2)';
        $this->assertEquals(
            new \T4\MVC\Route(['module' => '', 'controller' => 'Index', 'action' => 'act', 'params' => ['a' => 1, 'b' => 2]]),
            $reflector->invoke($router, $url)
        );
        $url = '///(a=1,b=2)';
        $this->assertEquals(
            new \T4\MVC\Route(['module' => '', 'controller' => 'Index', 'action' => 'default', 'params' => ['a' => 1, 'b' => 2]]),
            $reflector->invoke($router, $url)
        );
    }

    public function testScanExternalPath() {
        $router = \T4\MVC\Router::getInstance();
        $reflector = new ReflectionMethod($router, 'scanExternalPath');
        $reflector->setAccessible(true);

        $url = '/';
        $template = '/';
        $this->assertEquals(
            '/',
            $reflector->invoke($router, $url, $template)
        );
        $url = '/.html';
        $template = '/';
        $this->assertEquals(
            '/',
            $reflector->invoke($router, $url, $template)
        );
        $url = '/.json';
        $template = '/';
        $this->assertEquals(
            '/',
            $reflector->invoke($router, $url, $template)
        );
        $url = 'index';
        $template = 'index';
        $this->assertEquals(
            'index',
            $reflector->invoke($router, $url, $template)
        );
        $url = 'index.html';
        $template = 'index';
        $this->assertEquals(
            'index',
            $reflector->invoke($router, $url, $template)
        );
        $url = 'index.json';
        $template = 'index';
        $this->assertEquals(
            'index',
            $reflector->invoke($router, $url, $template)
        );

    }

}