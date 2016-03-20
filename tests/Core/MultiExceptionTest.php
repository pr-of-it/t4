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
        $errors = new MultiException;
        $this->assertInstanceOf(
            MultiException::class,
            $errors
        );
        $this->assertInstanceOf(
            T4\Core\ICollection::class,
            $errors
        );
        $this->assertInstanceOf(
            T4\Core\IArrayAccess::class,
            $errors
        );
        $this->assertTrue($errors->isEmpty());
    }

    public function testAppend()
    {
        $errors = new MultiException;
        $this->assertTrue($errors->isEmpty());

        $errors->append(new Exception('First'));
        $this->assertFalse($errors->isEmpty());
        $this->assertEquals(1, $errors->count());

        $errors->append(new Exception('Second'));
        $this->assertFalse($errors->isEmpty());
        $this->assertEquals(2, $errors->count());

        $this->assertEquals(
            [new Exception('First'), new Exception('Second')],
            $errors->toArray()
        );
    }

    public function testPrepend()
    {
        $errors = new MultiException;
        $this->assertTrue($errors->isEmpty());

        $errors->prepend(new Exception('First'));
        $this->assertFalse($errors->isEmpty());
        $this->assertEquals(1, $errors->count());

        $errors->prepend(new Exception('Second'));
        $this->assertFalse($errors->isEmpty());
        $this->assertEquals(2, $errors->count());

        $this->assertEquals(
            [new Exception('Second'), new Exception('First')],
            $errors->toArray()
        );
    }

    public function testAdd()
    {
        $errors = new MultiException;
        $this->assertTrue($errors->isEmpty());

        $errors->add(new Exception('First'));
        $this->assertFalse($errors->isEmpty());
        $this->assertEquals(1, $errors->count());

        $errors->add(new Exception('Second'));
        $this->assertFalse($errors->isEmpty());
        $this->assertEquals(2, $errors->count());

        $errors->addException('Third', 123);
        $this->assertFalse($errors->isEmpty());
        $this->assertEquals(3, $errors->count());

        $this->assertInstanceOf(
            \T4\Core\Exception::class,
            $errors[0]
        );
        $this->assertInstanceOf(
            \T4\Core\Exception::class,
            $errors[1]
        );
        $this->assertInstanceOf(
            \T4\Core\Exception::class,
            $errors[2]
        );
        $this->assertEquals(new Exception('First'), $errors[0]);
        $this->assertEquals(new Exception('Second'), $errors[1]);
        $this->assertEquals(new Exception('Third', 123), $errors[2]);
    }

    public function testCount()
    {
        $exception = new MultiException;
        $this->assertEquals(0, $exception->count());
        $exception->add(new Exception('foo'));
        $this->assertEquals(1, $exception->count());

        $exception = new MultiException;
        $this->assertEquals(0, count($exception));
        $exception->add(new Exception('foo'));
        $this->assertEquals(1, count($exception));
    }

    public function testClass()
    {
        $errors = new MultiException(SomeException::class);
        $errors->addException('Foo');
        $this->assertInstanceOf(
            SomeException::class,
            $errors[0]
        );
    }

    /**
     * @expectedException Exception
     */
    public function testInvalidClass()
    {
        $errors = new MultiException('SomeException');
        $errors->add(new Exception);
    }


    public function testIterate()
    {
        $errors = new MultiException();
        $errors->add(new Exception('Foo'));
        $errors->add(new Exception('Bar'));
        $errors->add(new Exception('Baz'));

        $i = 0;
        foreach ($errors as $ex) {
            $this->assertInstanceOf('T4\Core\Exception', $ex);
            $i++;
        }
        $this->assertEquals(3, $i);
    }

    public function testThrow()
    {
        try {

            $errors = new MultiException();
            $errors->add(new Exception('Foo'));
            $errors->add(new Exception('Bar'));
            $errors->add(new Exception('Baz'));

            if (!$errors->isEmpty())
                throw $errors;

        } catch (MultiException $ex) {

            $this->assertEquals(3, $ex->count());

            $this->assertInstanceOf(
                \T4\Core\Exception::class,
                $ex[0]
            );
            $this->assertInstanceOf(
                \T4\Core\Exception::class,
                $ex[1]
            );
            $this->assertInstanceOf(
                \T4\Core\Exception::class,
                $ex[2]
            );

            $this->assertEquals('Foo', $ex[0]->getMessage());
            $this->assertEquals('Bar', $ex[1]->getMessage());
            $this->assertEquals('Baz', $ex[2]->getMessage());

        }
    }

}