<?php

require_once realpath(__DIR__ . '/../../framework/boot.php');

class MemcacheTest extends PHPUnit_Framework_TestCase
{

    public function testCache()
    {
        $config = new \T4\Core\Config(['host' => 'localhost']);
        $cache = new \T4\Cache\Memcache($config);

        $key = 'Test';
        $time = 1;
        $src1 = function () {
            return 'Hello, world!';
        };

        $this->assertEquals(
            'Hello, world!',
            $cache($key, $src1, $time)
        );

        $src2 = function () {
            return 'Мир, труд, май!';
        };

        $this->assertEquals(
            'Hello, world!',
            $cache($key, $src2, $time)
        );
        sleep(2);
        $this->assertEquals(
            'Мир, труд, май!',
            $cache($key, $src2, $time)
        );

    }

}