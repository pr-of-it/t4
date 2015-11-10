<?php

require_once realpath(__DIR__ . '/../../framework/boot.php');

class TestAA
    implements \T4\Core\IArrayAccess
{
    use \T4\Core\TArrayAccess;
}

class TestTArrayAccess extends PHPUnit_Framework_TestCase
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

        unset($arr[1]);
        $this->assertFalse(isset($arr[1]));
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

    public function testIArrayable()
    {
        $arr1 = new TestAA();
        $arr1->fromArray([1 => 'foo', 'bar', 'baz']);

        $this->assertEquals('foo', $arr1[1]);
        $this->assertEquals('bar', $arr1[2]);
        $this->assertEquals('baz', $arr1[3]);

        $arr2 = new TestAA();
        $arr2['foo'] = 100;
        $arr2['bar'] = 200;
        $arr2['baz'] = 300;
        $this->assertEquals(
            ['foo' => 100, 'bar' => 200, 'baz' => 300],
            $arr2->toArray()
        );
    }

    public function testIterator()
    {
        $arr = (new TestAA())->fromArray([1, 2, 3]);
        $test = [1, 2, 3];
        $i = 0;
        foreach ($arr as $k => $v) {
            $this->assertEquals($test[$k], $v);
            $i++;
        }
        $this->assertEquals(3, $i);
    }

}
