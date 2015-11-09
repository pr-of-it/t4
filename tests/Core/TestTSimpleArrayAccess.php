<?php

require_once realpath(__DIR__ . '/../../framework/boot.php');

class TestAA
    implements ArrayAccess, Countable
{
    use \T4\Core\TSimpleArrayAccess;
}

class TestTSimpleArrayAccess extends PHPUnit_Framework_TestCase
{

    public function testSetGet()
    {
        $arr = new TestAA();
        $this->assertFalse($arr->offsetExists(1));
        $this->assertFalse(isset($arr[1]));
        $this->assertNull($arr[1]);

        $arr[1] = 100;
        $this->assertTrue($arr->offsetExists(1));
        $this->assertTrue(isset($arr[1]));
        $this->assertEquals(100, $arr[1]);
        $this->assertEquals(100, $arr->offsetGet(1));

        $arr->offsetSet(2, 200);
        $this->assertTrue($arr->offsetExists(2));
        $this->assertTrue(isset($arr[2]));
        $this->assertEquals(200, $arr[2]);
        $this->assertEquals(200, $arr->offsetGet(2));

        unset($arr[1]);
        $this->assertFalse($arr->offsetExists(1));
        $this->assertFalse(isset($arr[1]));
        $this->assertNull($arr[1]);

        $arr->offsetUnset(2);
        $this->assertFalse($arr->offsetExists(2));
        $this->assertFalse(isset($arr[2]));
        $this->assertNull($arr[2]);
    }

    public function testNullIsset()
    {
        $arr = new TestAA();
        $this->assertFalse(isset($arr[1]));

        $arr[1] = null;
        $this->assertTrue(isset($arr[1]));
        $this->assertNull($arr[1]);
    }

    public function testCount()
    {
        $arr = new TestAA();
        $this->assertEquals(0, $arr->count());
        $this->assertEquals(0, count($arr));
        $this->assertTrue($arr->isEmpty());

        $arr[] = 42;
        $this->assertEquals(1, $arr->count());
        $this->assertEquals(1, count($arr));

        $arr[10] = 100;
        $this->assertEquals(2, $arr->count());
        $this->assertEquals(2, count($arr));
        $this->assertFalse($arr->isEmpty());

        unset($arr[0]);
        $this->assertEquals(1, $arr->count());
        $this->assertEquals(1, count($arr));
        $this->assertFalse($arr->isEmpty());

        unset($arr[10]);
        $this->assertTrue($arr->isEmpty());
    }

}
