<?php

require_once realpath(__DIR__ . '/../../framework/boot.php');

class RequestTest extends PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        $_SERVER['argv'] = ['t4'];
        $request = new \T4\Console\Request();

        $this->assertEmpty($request->command);
        $this->assertEmpty($request->arguments);
        $this->assertEmpty($request->options);

        $_SERVER['argv'] = ['t4', 'test'];
        $request = new \T4\Console\Request();

        $this->assertEquals('test', $request->command);
        $this->assertEmpty($request->arguments);
        $this->assertEmpty($request->options);

        $_SERVER['argv'] = ['t4', 'test', '--foo=bar'];
        $request = new \T4\Console\Request();

        $this->assertEquals('test', $request->command);
        $this->assertEquals(['--foo=bar'], $request->arguments);
        $this->assertEquals(new \T4\Console\Request(['foo' => 'bar']), $request->options);

        $_SERVER['argv'] = ['t4', 'test', '--foo=bar', 'blabla'];
        $request = new \T4\Console\Request();

        $this->assertEquals('test', $request->command);
        $this->assertEquals(['--foo=bar', 'blabla'], $request->arguments);
        $this->assertEquals(new \T4\Console\Request(['foo' => 'bar']), $request->options);

        $_SERVER['argv'] = ['t4', 'test', '--foo=bar', 'blabla', '--baz'];
        $request = new \T4\Console\Request();

        $this->assertEquals('test', $request->command);
        $this->assertEquals(['--foo=bar', 'blabla', '--baz'], $request->arguments);
        $this->assertEquals(new \T4\Console\Request(['foo' => 'bar', 'baz' => true]), $request->options);
    }

}
 