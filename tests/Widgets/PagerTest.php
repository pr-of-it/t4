<?php

namespace T4\Tests\Widgets;

use T4\Widgets\Pager;

require_once realpath(__DIR__ . '/../../framework/boot.php');

class PagerTest extends \PHPUnit_Framework_TestCase
{

    public function testRender()
    {
        $pager = new Pager([
            'total' => 7,
            'url'   => 'http://example.com/test.html?foo=1&bar[baz]=2&page=%d',
        ]);

        $getLink = new \ReflectionMethod($pager, 'getPageLink');
        $getLink->setAccessible(true);
        $link = $getLink->invokeArgs($pager, ['page' => 3]);

        $this->assertEquals('http://example.com/test.html?foo=1&bar%5Bbaz%5D=2&page=3', $link);
    }

}