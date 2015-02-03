<?php

namespace {
    require_once realpath(__DIR__ . '/../../framework/boot.php');
}

namespace App\Commands {

    use T4\Console\Command;

    class Test extends Command
    {

    }
}

namespace {

    use T4\Console\Request;

    class ApplicationTest extends PHPUnit_Framework_TestCase
    {

        public function testParseRequest()
        {
            $appClass = new ReflectionClass(\T4\Console\Application::class);
            $app = $appClass->newInstanceWithoutConstructor();
            $reflector = new ReflectionMethod($app, 'parseRequest');
            $reflector->setAccessible(true);

            $_SERVER['argv'] = ['t4', 'test'];
            $request = new Request();
            $this->assertEquals(
                ['namespace' => 'App', 'command' => 'Test', 'action' => 'Default', 'options' => []],
                $reflector->invoke($app, $request)
            );

            $_SERVER['argv'] = ['t4', '/foo/bar'];
            $request = new Request();
            $this->assertEquals(
                ['namespace' => 'T4', 'command' => 'Foo', 'action' => 'Bar', 'options' => []],
                $reflector->invoke($app, $request)
            );

            $_SERVER['argv'] = ['t4', '/foo/bar', '--baz=test'];
            $request = new Request();
            $this->assertEquals(
                ['namespace' => 'T4', 'command' => 'Foo', 'action' => 'Bar', 'options' => new Request(['baz' => 'test'])],
                $reflector->invoke($app, $request)
            );

            $_SERVER['argv'] = ['t4', '/foo/bar', '--baz=test', '--aaa'];
            $request = new Request();
            $this->assertEquals(
                ['namespace' => 'T4', 'command' => 'Foo', 'action' => 'Bar', 'options' => new Request(['baz' => 'test', 'aaa' => true])],
                $reflector->invoke($app, $request)
            );
        }

    }

}