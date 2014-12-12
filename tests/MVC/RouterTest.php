<?php

use T4\Mvc\Route;

require_once realpath(__DIR__ . '/../../framework/boot.php');

function getRouteConfig()
{
    return new \T4\Core\Std([
        '/'=>'///',
        '/index'=>'///',
        '/goods'=>'/Shop/Goods/default',
        '/goods/<1>'=>'/Shop/Goods/view(id=<1>)',
        'auto.<1>!/shop/<3>/<2>'=>'/Shop/Goods/view(lang=<1>,id=<2>,vendor=<3>)',
        '/shop/<2>/<1>'=>'/Shop/Goods/view(id=<1>,vendor=<2>)',
    ]);
}

class RouterTest extends PHPUnit_Framework_TestCase
{

    public function testSplitRequestPath()
    {
        $router = \T4\Mvc\Router::getInstance();
        $reflector = new ReflectionMethod($router, 'splitRequestPath');
        $reflector->setAccessible(true);

        $url = '/';
        $this->assertEquals(
            new Route(['domain'=>null, 'basepath' => '/', 'extension' => '']),
            $reflector->invoke($router, $url)
        );
        $url = '/.html';
        $this->assertEquals(
            new Route(['domain'=>null, 'basepath' => '/', 'extension' => 'html']),
            $reflector->invoke($router, $url)
        );
        $url = '/.json';
        $this->assertEquals(
            new Route(['domain'=>null, 'basepath' => '/', 'extension' => 'json']),
            $reflector->invoke($router, $url)
        );
        $url = '/index';
        $this->assertEquals(
            new Route(['domain'=>null, 'basepath' => '/index', 'extension' => '']),
            $reflector->invoke($router, $url)
        );
        $url = '/index.html';
        $this->assertEquals(
            new Route(['domain'=>null, 'basepath' => '/index', 'extension' => 'html']),
            $reflector->invoke($router, $url)
        );
        $url = '/index.json';
        $this->assertEquals(
            new Route(['domain'=>null, 'basepath' => '/index', 'extension' => 'json']),
            $reflector->invoke($router, $url)
        );

        $url = '/shop/goods.html';
        $this->assertEquals(
            new Route(['domain'=>null, 'basepath' => '/shop/goods', 'extension' => 'html']),
            $reflector->invoke($router, $url)
        );
        $url = '/shop/goods.html?foo=bar';
        $this->assertEquals(
            new Route(['domain'=>null, 'basepath' => '/shop/goods', 'extension' => 'html']),
            $reflector->invoke($router, $url)
        );

        $url = '/shop/goods.badextension';
        $this->assertEquals(
            new Route(['domain'=>null, 'basepath' => '/shop/goods', 'extension' => '']),
            $reflector->invoke($router, $url)
        );

        $url = 'foo.local!/';
        $this->assertEquals(
            new Route(['domain'=>'foo.local', 'basepath' => '/', 'extension' => '']),
            $reflector->invoke($router, $url)
        );
        $url = 'bar.local!/.html';
        $this->assertEquals(
            new Route(['domain'=>'bar.local', 'basepath' => '/', 'extension' => 'html']),
            $reflector->invoke($router, $url)
        );
        $url = 'foo.local!/index';
        $this->assertEquals(
            new Route(['domain'=>'foo.local', 'basepath' => '/index', 'extension' => '']),
            $reflector->invoke($router, $url)
        );
        $url = 'foo.local!/index.html';
        $this->assertEquals(
            new Route(['domain'=>'foo.local', 'basepath' => '/index', 'extension' => 'html']),
            $reflector->invoke($router, $url)
        );
        $url = 'bar.local!/shop/goods.badextension';
        $this->assertEquals(
            new Route(['domain'=>'bar.local', 'basepath' => '/shop/goods', 'extension' => '']),
            $reflector->invoke($router, $url)
        );
    }

    public function testGetTemplateMatches()
    {
        $router = \T4\Mvc\Router::getInstance();
        $reflector = new ReflectionMethod($router, 'getTemplateMatches');
        $reflector->setAccessible(true);

        $template = '/foo/bar';
        $path = '/';
        $this->assertEquals(
            false,
            $reflector->invoke($router, $template, $path)
        );
        $template = '/';
        $path = '/foo/bar';
        $this->assertEquals(
            false,
            $reflector->invoke($router, $template, $path)
        );

        $template = '/';
        $path = '/';
        $this->assertEquals(
            [],
            $reflector->invoke($router, $template, $path)
        );
        $template = '/index';
        $path = '/index';
        $this->assertEquals(
            [],
            $reflector->invoke($router, $template, $path)
        );

        $template = '/index/<1>/<2>';
        $path = '/index/foo';
        $this->assertEquals(
            false,
            $reflector->invoke($router, $template, $path)
        );
        $template = '/index/<1>';
        $path = '/index/foo/bar';
        $this->assertEquals(
            [1=>'foo/bar'],
            $reflector->invoke($router, $template, $path)
        );
        $template = '/index/<1>/<2>';
        $path = '/index/foo/bar';
        $this->assertEquals(
            [1=>'foo', 2=>'bar'],
            $reflector->invoke($router, $template, $path)
        );
    }

    public function testMatchPathTemplate()
    {
        $router = \T4\Mvc\Router::getInstance();
        $reflector = new ReflectionMethod($router, 'matchPathTemplate');
        $reflector->setAccessible(true);

        $template = '/';
        $path = new Route(['domain'=>null, 'basepath'=>'/']);
        $this->assertEquals(
            [],
            $reflector->invoke($router, $template, $path)
        );

        $template = '/goods';
        $path = new Route(['domain'=>null, 'basepath'=>'/']);
        $this->assertEquals(
            false,
            $reflector->invoke($router, $template, $path)
        );

        $template = '/goods';
        $path = new Route(['domain'=>null, 'basepath'=>'/index']);
        $this->assertEquals(
            false,
            $reflector->invoke($router, $template, $path)
        );

        $template = '/goods/<1>';
        $path = new Route(['domain'=>null, 'basepath'=>'/goods/13']);
        $this->assertEquals(
            [1=>13],
            $reflector->invoke($router, $template, $path)
        );

        $template = '/goods/<2>/<1>';
        $path = new Route(['domain'=>null, 'basepath'=>'/goods/cars/volvo']);
        $this->assertEquals(
            [1=>'volvo', 2=>'cars'],
            $reflector->invoke($router, $template, $path)
        );

        $template = 'test.local!/goods';
        $path = new Route(['domain'=>null, 'basepath'=>'/']);
        $this->assertEquals(
            false,
            $reflector->invoke($router, $template, $path)
        );

        $template = 'test.local!/goods';
        $path = new Route(['domain'=>null, 'basepath'=>'/goods']);
        $this->assertEquals(
            false,
            $reflector->invoke($router, $template, $path)
        );

        $template = 'test.local!/goods';
        $path = new Route(['domain'=>'test.local', 'basepath'=>'/']);
        $this->assertEquals(
            false,
            $reflector->invoke($router, $template, $path)
        );

        $template = 'foo.bar/goods';
        $path = new Route(['domain'=>'foo.bar', 'basepath'=>'/index']);
        $this->assertEquals(
            false,
            $reflector->invoke($router, $template, $path)
        );

        $template = '<1>.local!/goods/<2>';
        $path = new Route(['domain'=>'test.local', 'basepath'=>'/goods/13']);
        $this->assertEquals(
            [1=>'test', 2=>13],
            $reflector->invoke($router, $template, $path)
        );

        $template = 'auto.<1>!/goods/<3>/<2>';
        $path = new Route(['domain'=>'auto.fr', 'basepath'=>'/goods/cars/volvo']);
        $this->assertEquals(
            [1=>'fr', 2=>'volvo', 3=>'cars'],
            $reflector->invoke($router, $template, $path)
        );

    }

    public function testSplitInternalPath()
    {
        $router = \T4\Mvc\Router::getInstance();
        $reflector = new ReflectionMethod($router, 'splitInternalPath');
        $reflector->setAccessible(true);

        $url = '/mod/ctrl/act';
        $this->assertEquals(
            new \T4\Mvc\Route(['module' => 'Mod', 'controller' => 'Ctrl', 'action' => 'Act', 'params' => []]),
            $reflector->invoke($router, $url)
        );
        $url = '//ctrl/act';
        $this->assertEquals(
            new \T4\Mvc\Route(['module' => '', 'controller' => 'Ctrl', 'action' => 'Act', 'params' => []]),
            $reflector->invoke($router, $url)
        );
        $url = '/mod//act';
        $this->assertEquals(
            new \T4\Mvc\Route(['module' => 'Mod', 'controller' => 'Index', 'action' => 'Act', 'params' => []]),
            $reflector->invoke($router, $url)
        );
        $url = '/mod/ctrl/';
        $this->assertEquals(
            new \T4\Mvc\Route(['module' => 'Mod', 'controller' => 'Ctrl', 'action' => 'Default', 'params' => []]),
            $reflector->invoke($router, $url)
        );
        $url = '///act';
        $this->assertEquals(
            new \T4\Mvc\Route(['module' => '', 'controller' => 'Index', 'action' => 'Act', 'params' => []]),
            $reflector->invoke($router, $url)
        );
        $url = '///';
        $this->assertEquals(
            new \T4\Mvc\Route(['module' => '', 'controller' => 'Index', 'action' => 'Default', 'params' => []]),
            $reflector->invoke($router, $url)
        );

        $url = '/mod/ctrl/act(a=1)';
        $this->assertEquals(
            new \T4\Mvc\Route(['module' => 'Mod', 'controller' => 'Ctrl', 'action' => 'Act', 'params' => ['a' => 1]]),
            $reflector->invoke($router, $url)
        );
        $url = '/mod/ctrl/act(a=1,b=2)';
        $this->assertEquals(
            new \T4\Mvc\Route(['module' => 'Mod', 'controller' => 'Ctrl', 'action' => 'Act', 'params' => ['a' => 1, 'b' => 2]]),
            $reflector->invoke($router, $url)
        );
        $url = '//ctrl/act(a=1,b=2)';
        $this->assertEquals(
            new \T4\Mvc\Route(['module' => '', 'controller' => 'Ctrl', 'action' => 'Act', 'params' => ['a' => 1, 'b' => 2]]),
            $reflector->invoke($router, $url)
        );
        $url = '/mod//act(a=1,b=2)';
        $this->assertEquals(
            new \T4\Mvc\Route(['module' => 'Mod', 'controller' => 'Index', 'action' => 'Act', 'params' => ['a' => 1, 'b' => 2]]),
            $reflector->invoke($router, $url)
        );
        $url = '/mod/ctrl/(a=1,b=2)';
        $this->assertEquals(
            new \T4\Mvc\Route(['module' => 'Mod', 'controller' => 'Ctrl', 'action' => 'Default', 'params' => ['a' => 1, 'b' => 2]]),
            $reflector->invoke($router, $url)
        );
        $url = '///act(a=1,b=2)';
        $this->assertEquals(
            new \T4\Mvc\Route(['module' => '', 'controller' => 'Index', 'action' => 'Act', 'params' => ['a' => 1, 'b' => 2]]),
            $reflector->invoke($router, $url)
        );
        $url = '///(a=1,b=2)';
        $this->assertEquals(
            new \T4\Mvc\Route(['module' => '', 'controller' => 'Index', 'action' => 'Default', 'params' => ['a' => 1, 'b' => 2]]),
            $reflector->invoke($router, $url)
        );
    }

    public function testParseUrl()
    {

        $router = \T4\Mvc\Router::getInstance();
        $router->setConfig(getRouteConfig());

        $this->assertEquals(
            new \T4\Mvc\Route(['module'=>'', 'controller'=>'Index', 'action'=>'Default', 'params'=>[], 'format'=>'html']),
            $router->parseRequestPath('/')
        );
        $this->assertEquals(
            new \T4\Mvc\Route(['module'=>'Shop', 'controller'=>'Goods', 'action'=>'Default', 'params'=>[], 'format'=>'html']),
            $router->parseRequestPath('/goods')
        );
        $this->assertEquals(
            new \T4\Mvc\Route(['module'=>'Shop', 'controller'=>'Goods', 'action'=>'View', 'params'=>['id'=>13], 'format'=>'html']),
            $router->parseRequestPath('/goods/13.html')
        );
        $this->assertEquals(
            new \T4\Mvc\Route(['module'=>'Shop', 'controller'=>'Goods', 'action'=>'View', 'params'=>['id'=>'42', 'vendor'=>'volvo'], 'format'=>'html']),
            $router->parseRequestPath('/shop/volvo/42.html')
        );
        $this->assertEquals(
            new \T4\Mvc\Route(['module'=>'Shop', 'controller'=>'Goods', 'action'=>'View', 'params'=>['lang'=>'fr', 'id'=>'42', 'vendor'=>'volvo'], 'format'=>'html']),
            $router->parseRequestPath('auto.fr!/shop/volvo/42.html')
        );
    }

}