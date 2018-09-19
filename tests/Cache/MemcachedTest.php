<?php

require_once realpath(__DIR__ . '/../../framework/boot.php');

class MemcachedTest extends \PHPUnit\Framework\TestCase
{

    public function testCache(): void
    {
        $config = new \T4\Core\Config(['host' => 'localhost']);
        $cache = new \T4\Cache\Memcached($config);

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
