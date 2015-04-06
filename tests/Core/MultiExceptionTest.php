<?php

use T4\Core\Exception;
use T4\Core\MultiException;

require_once realpath(__DIR__ . '/../../framework/boot.php');

class SomeException extends Exception
{
}

class MultiExceptionTest extends PHPUnit_Framework_TestCase
{

    public function testCreate()
    {
        $exception = new MultiException;
        $this->assertInstanceOf(
            'T4\Core\MultiException',
            $exception
        );
        $this->assertInstanceOf(
            'T4\Core\Collection',
            $exception->getExceptions()
        );
        $this->assertTrue($exception->isEmpty());
    }

    public function testCount()
    {
        $exception = new MultiException;
        $this->assertEquals(0, $exception->count());
        $exception->add('foo');
        $this->assertEquals(1, $exception->count());

        $exception = new MultiException;
        $this->assertEquals(0, count($exception));
        $exception->add('foo');
        $this->assertEquals(1, count($exception));
    }

    public function testAdd()
    {
        $exception = new MultiException;

        $exception->add(new Exception('First'));
        $this->assertFalse($exception->isEmpty());
        $this->assertEquals(1, $exception->count());

        $exception->add(new Exception('Second'));
        $this->assertFalse($exception->isEmpty());
        $this->assertEquals(2, $exception->count());

        $exception->add('Second', 123);
        $this->assertFalse($exception->isEmpty());
        $this->assertEquals(3, $exception->count());

        $this->assertInstanceOf(
            'T4\Core\Collection',
            $exception->getExceptions()
        );
        $this->assertInstanceOf(
            'T4\Core\Exception',
            $exception->getExceptions()[0]
        );
        $this->assertInstanceOf(
            'T4\Core\Exception',
            $exception->getExceptions()[1]
        );
        $this->assertInstanceOf(
            'T4\Core\Exception',
            $exception->getExceptions()[2]
        );
    }

    public function testClass()
    {
        $exception = new MultiException('SomeException');
        $exception->add('Foo');
        $this->assertInstanceOf(
            'SomeException',
            $exception->getExceptions()[0]
        );
    }

    /**
     * @expectedException Exception
     */
    public function testInvalidClass()
    {
        $exception = new MultiException('SomeException');
        $exception->add(new Exception);
    }

    public function testIterate()
    {
        $exception = new MultiException();
        $exception->add('Foo');
        $exception->add('Bar');
        $exception->add('Baz');

        foreach ($exception as $ex) {
            $this->assertInstanceOf('T4\Core\Exception', $ex);
        }
    }

    public function testThrow()
    {
        try {

            $exception = new MultiException();
            $exception->add('Foo');
            $exception->add('Bar');
            $exception->add('Baz');

            if (!$exception->isEmpty())
                throw $exception;

        } catch (MultiException $ex) {

            $this->assertEquals(3, $ex->count());

            $exceptions = $ex->getExceptions();

            $this->assertInstanceOf(
                'T4\Core\Exception',
                $exceptions[0]
            );
            $this->assertInstanceOf(
                'T4\Core\Exception',
                $exceptions[1]
            );
            $this->assertInstanceOf(
                'T4\Core\Exception',
                $exceptions[2]
            );

            $this->assertEquals('Foo', $exceptions[0]->getMessage());
            $this->assertEquals('Bar', $exceptions[1]->getMessage());
            $this->assertEquals('Baz', $exceptions[2]->getMessage());

        }
    }

}