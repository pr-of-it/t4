<?php

require_once realpath(__DIR__ . '/../../framework/boot.php');

function getLambdaRouteConfig()
{
    return new \T4\Core\Std([
        '/' => '///',
        '/index' => '///',
        '/goods' => '/Shop/Goods/default',
        '/goods/<1>' => function ($request, $matches) {
            return '/Shop/Goods/view(id=' . $matches[1] . ')';
        },
        'auto.<1>!/shop/<3>/<2>' => function ($request, $matches) {
            return new \T4\Mvc\Route([
                'module' => 'Shop',
                'controller' => 'Goods',
                'action' => 'View',
                'params' => [
                    'lang' => str_replace('auto.', '', $request['domain']),
                    'id' => $matches[2],
                    'vendor' => $matches[3],
                ],
            ]);
        },
        '/items/<1>' => function ($request, $matches) {
            return new \T4\Mvc\Route([
                'module' => 'Shop',
                'controller' => 'Items',
                'action' => 'View',
                'format' => 'json',
            ]);
        },
        '/shop/<2>/<1>' => '/Shop/Goods/view(id=<1>,vendor=<2>)',
    ]);
}

class RouterLambdaTest extends PHPUnit_Framework_TestCase
{

    public function testParseUrl()
    {

        $router = \T4\Mvc\Router::getInstance();
        $reflector = new ReflectionMethod($router, 'parseRequestPath');
        $reflector->setAccessible(true);
        $router->setConfig(getLambdaRouteConfig());

        $url = '/';
        $this->assertEquals(
            new \T4\Mvc\Route(['module' => '', 'controller' => 'Index', 'action' => 'Default', 'params' => [], 'format' => 'html']),
            $reflector->invoke($router, $url)
        );
        $url = '/goods';
        $this->assertEquals(
            new \T4\Mvc\Route(['module' => 'Shop', 'controller' => 'Goods', 'action' => 'Default', 'params' => [], 'format' => 'html']),
            $reflector->invoke($router, $url)
        );
        $url = '/goods/13.html';
        $this->assertEquals(
            new \T4\Mvc\Route(['module' => 'Shop', 'controller' => 'Goods', 'action' => 'View', 'params' => ['id' => 13], 'format' => 'html']),
            $reflector->invoke($router, $url)
        );
        $url = '/shop/volvo/42.html';
        $this->assertEquals(
            new \T4\Mvc\Route(['module' => 'Shop', 'controller' => 'Goods', 'action' => 'View', 'params' => ['id' => '42', 'vendor' => 'volvo'], 'format' => 'html']),
            $reflector->invoke($router, $url)
        );
        $url = 'auto.fr!/shop/volvo/42.html';
        $this->assertEquals(
            new \T4\Mvc\Route(['module' => 'Shop', 'controller' => 'Goods', 'action' => 'View', 'params' => ['lang' => 'fr', 'id' => '42', 'vendor' => 'volvo'], 'format' => 'html']),
            $reflector->invoke($router, $url)
        );
        $url = '/items/45.html';
        $this->assertEquals(
            new \T4\Mvc\Route(['module' => 'Shop', 'controller' => 'Items', 'action' => 'View', 'format' => 'json']),
            $reflector->invoke($router, $url)
        );
    }

}