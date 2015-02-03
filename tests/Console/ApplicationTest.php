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


    class ApplicationTest extends PHPUnit_Framework_TestCase
    {

        public function testParseCmd()
        {
            $appClass = new ReflectionClass(\T4\Console\Application::class);
            $app = $appClass->newInstanceWithoutConstructor();
            $reflector = new ReflectionMethod($app, 'parseCmd');
            $reflector->setAccessible(true);

            $this->assertEquals(
                ['namespace' => 'App', 'command' => 'Test', 'action' => 'Default', 'params' => []],
                $reflector->invoke($app, ['test'])
            );

            $this->assertEquals(
                ['namespace' => 'T4', 'command' => 'Foo', 'action' => 'Bar', 'params' => []],
                $reflector->invoke($app, ['/foo/bar'])
            );

            $this->assertEquals(
                ['namespace' => 'T4', 'command' => 'Foo', 'action' => 'Bar', 'params' => ['baz' => 'test']],
                $reflector->invoke($app, ['/foo/bar', '--baz=test'])
            );

            $this->assertEquals(
                ['namespace' => 'T4', 'command' => 'Foo', 'action' => 'Bar', 'params' => ['baz' => 'test', 'aaa' => true]],
                $reflector->invoke($app, ['/foo/bar', '--baz=test', '--aaa'])
            );
        }

    }

}
