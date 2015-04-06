<?php

require_once realpath(__DIR__ . '/../../framework/boot.php');

class HelpersTest extends PHPUnit_Framework_TestCase
{

    public $testFileName;

    public function setUp()
    {
        $this->testFileName = __DIR__ . '/test.txt';
    }

    public function testAsyncRun()
    {
        $testFileName = $this->testFileName;
        $this->assertFalse(file_exists($testFileName));

        \T4\Threads\Helpers::run(function ($text) use ($testFileName) {
            sleep(10);
            file_put_contents($testFileName, $text);
        }, ['text' => 'Hello, world!']);

        sleep(5);
        $this->assertFalse(file_exists($testFileName));
        sleep(7);
        $this->assertTrue(file_exists($testFileName));
        $this->assertEquals(
            'Hello, world!',
            file_get_contents($testFileName)
        );
    }

    public function tearDown()
    {
        unlink($this->testFileName);
    }

}
 