<?php

namespace T4\Tests\Widgets;

use T4\Core\QueryString;
use T4\Mvc\Application;
use T4\Widgets\Pager;

require_once realpath(__DIR__ . '/../../framework/boot.php');

class PagerTest extends \PHPUnit\Framework\TestCase
{

    public function testGetPageLink()
    {
        $pager = new Pager([
            'total' => 7,
            'url'   => 'http://example.com/test.html?foo=1&bar[baz]=2&page=%d',
        ]);
        $getLink = new \ReflectionMethod($pager, 'getPageLink');
        $getLink->setAccessible(true);
        $link = $getLink->invokeArgs($pager, ['page' => 3]);
        $this->assertEquals('http://example.com/test.html?foo=1&bar%5Bbaz%5D=2&page=3', $link);

        $oldServerArray = $_SERVER;
        $_SERVER = [
            'SERVER_PORT'  => '80',
            'HTTP_HOST'    => 'example.com',
            'REQUEST_URI'  => '/test.html?foo=1&bar%5Bbaz%5D=2',
            'REQUEST_TIME' => \time(),
        ];
        $pager = new Pager(['total' => 7]);
        $link = $getLink->invokeArgs($pager, ['page' => 3]);
        $this->assertEquals('http://example.com/test.html?foo=1&bar%5Bbaz%5D=2&page=3', $link);

        Application::instance()->request->url->query = new QueryString();
        $pager = new Pager(['total' => 7]);
        $link = $getLink->invokeArgs($pager, ['page' => 3]);
        $_SERVER = $oldServerArray;
        $this->assertEquals('http://example.com/test.html?page=3', $link);
    }

}