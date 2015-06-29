<?php

require_once realpath(__DIR__ . '/../../framework/boot.php');

class LocalTest extends PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
        foreach (glob(__DIR__ . DS . '*.cache') as $file) {
            unlink($file);
        }
    }

    public function testCache()
    {
        $config = new \T4\Core\Config(['path' => __DIR__]);
        $cache = new \T4\Cache\Local($config);

        $key = 'Test';
        $time = 1;
        $src1 = function () {
            return 'Hello, world!';
        };

        $this->assertEquals(
            'Hello, world!',
            $cache($key, $src1, $time)
        );

        $cacheFile = __DIR__ . DS . md5($key) . '.cache';
        $this->assertTrue(file_exists($cacheFile));
        $this->assertEquals(serialize($src1()), file_get_contents($cacheFile));

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
        $this->assertEquals(serialize($src2()), file_get_contents($cacheFile));

    }

    protected function tearDown()
    {
        foreach (glob(__DIR__ . DS . '*.cache') as $file) {
            unlink($file);
        }
    }

}
 