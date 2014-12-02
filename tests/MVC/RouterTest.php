<?php

require_once realpath(__DIR__ . '/../../framework/boot.php');

function getRouteConfig()
{
    return new \T4\Core\Std([
        '/'=>'///',
        'index'=>'///',
        'goods'=>'/Shop/Goods/default',
        'goods/<1>'=>'/Shop/Goods/view(id=<1>)',
        'shop/<2>/<1>'=>'/Shop/Goods/view(id=<1>,vendor=<2>)',
    ]);
}

class RouterTest extends PHPUnit_Framework_TestCase
{

    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     */
    public function testSplitExternalPath()
    {

        $router = \T4\Mvc\Router::getInstance();
        $reflector = new ReflectionMethod($router, 'splitExternalPath');
        $reflector->setAccessible(true);

        $url = '/';
        $this->assertEquals(
            new \T4\Mvc\Route(['base' => '/', 'extension' => '']),
            $reflector->invoke($router, $url)
        );
        $url = '/.html';
        $this->assertEquals(
            new \T4\Mvc\Route(['base' => '/', 'extension' => 'html']),
            $reflector->invoke($router, $url)
        );
        $url = '/.json';
        $this->assertEquals(
            new \T4\Mvc\Route(['base' => '/', 'extension' => 'json']),
            $reflector->invoke($router, $url)
        );
        $url = 'index';
        $this->assertEquals(
            new \T4\Mvc\Route(['base' => 'index', 'extension' => '']),
            $reflector->invoke($router, $url)
        );
        $url = 'index.html';
        $this->assertEquals(
            new \T4\Mvc\Route(['base' => 'index', 'extension' => 'html']),
            $reflector->invoke($router, $url)
        );
        $url = 'index.json';
        $this->assertEquals(
            new \T4\Mvc\Route(['base' => 'index', 'extension' => 'json']),
            $reflector->invoke($router, $url)
        );

        $url = 'shop/goods.html';
        $this->assertEquals(
            new \T4\Mvc\Route(['base' => 'shop/goods', 'extension' => 'html']),
            $reflector->invoke($router, $url)
        );

    }

    public function testMatchUrlTemplate()
    {

        $router = \T4\Mvc\Router::getInstance();
        $reflector = new ReflectionMethod($router, 'matchUrlTemplate');
        $reflector->setAccessible(true);

        $template = '/';
        $url = '/';
        $this->assertEquals(
            [],
            $reflector->invoke($router, $template, $url)
        );

        $template = 'goods';
        $url = '/';
        $this->assertEquals(
            false,
            $reflector->invoke($router, $template, $url)
        );

        $template = 'goods';
        $url = 'index';
        $this->assertEquals(
            false,
            $reflector->invoke($router, $template, $url)
        );

        $template = 'goods/<1>';
        $url = 'goods/13';
        $this->assertEquals(
            [1=>13],
            $reflector->invoke($router, $template, $url)
        );

        $template = 'goods/<2>/<1>';
        $url = 'goods/cars/volvo';
        $this->assertEquals(
            [1=>'volvo', 2=>'cars'],
            $reflector->invoke($router, $template, $url)
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
            $router->parseUrl('')
        );

        $this->assertEquals(
            new \T4\Mvc\Route(['module'=>'Shop', 'controller'=>'Goods', 'action'=>'Default', 'params'=>[], 'format'=>'html']),
            $router->parseUrl('goods')
        );

        $this->assertEquals(
            new \T4\Mvc\Route(['module'=>'Shop', 'controller'=>'Goods', 'action'=>'View', 'params'=>['id'=>13], 'format'=>'html']),
            $router->parseUrl('goods/13.html')
        );

        $this->assertEquals(
            new \T4\Mvc\Route(['module'=>'Shop', 'controller'=>'Goods', 'action'=>'View', 'params'=>['vendor'=>'volvo', 'id'=>42], 'format'=>'html']),
            $router->parseUrl('shop/volvo/42.html')
        );

    }

}