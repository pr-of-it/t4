<?php

require_once realpath(__DIR__ . '/../../framework/boot.php');

class TestStdClass1 extends \T4\Core\Std {

    protected function validateFoo($val)
    {
        return $val > 0;
    }

}

class TestStdClass2 extends \T4\Core\Std {

    protected function sanitizeFoo($val)
    {
        return $val * 10;
    }

}

class StdTest extends \PHPUnit\Framework\TestCase
{

    public function testAdd()
    {
        $obj = new \T4\Core\Std();

        $obj[] = 'foo';
        $obj[] = 'bar';
        $this->assertEquals($obj[0], 'foo');
        $this->assertEquals($obj[1], 'bar');

        $obj->append('baz');
        $this->assertEquals($obj[2], 'baz');
    }

    public function testConstructWithZeroKey()
    {
        $obj = new T4\Core\Std([1 => '1', 0 => '0', 2 => '2']);
        $this->assertCount(3, $obj);
    }

    public function testArrayAccess()
    {
        $obj = new T4\Core\Std();

        $obj->foo = 'bar';
        $this->assertEquals('bar', $obj->foo);
        $this->assertEquals('bar', $obj['foo']);

        $obj['baz'] = 'bla';
        $this->assertEquals('bla', $obj->baz);
        $this->assertEquals('bla', $obj['baz']);
    }

    public function testCountable()
    {

        $obj = new \T4\Core\Std();
        $this->assertEquals(0, count($obj));
        $obj->a = 1;
        $this->assertEquals(1, count($obj));
        $obj->b = 2;
        $this->assertEquals(2, count($obj));
        unset($obj->a);
        $this->assertEquals(1, count($obj));
    }

    public function testMerge()
    {
        $obj1 = new \T4\Core\Std(['foo' => 1]);
        $obj1->merge(['bar' => 2]);
        $this->assertEquals(1, $obj1->foo);
        $this->assertEquals(2, $obj1->bar);
        $this->assertEquals(new \T4\Core\Std(['foo' => 1, 'bar' => 2]), $obj1);

        $obj2 = new \T4\Core\Std(['foo' => 11]);
        $obj2->merge(new \T4\Core\Std(['bar' => 21]));
        $this->assertEquals(11, $obj2->foo);
        $this->assertEquals(21, $obj2->bar);
        $this->assertEquals(new \T4\Core\Std(['foo' => 11, 'bar' => 21]), $obj2);
    }

    public function testDataKey()
    {
        $obj = new \T4\Core\Std(['data' => 42]);
        $this->assertEquals(42, $obj->data);
    }

    public function testNumericOffsets()
    {
        $obj = new \T4\Core\Std();
        $obj[1] = 100;
        $obj->{2} = 200;
        $this->assertEquals(100, $obj[1]);
        $this->assertEquals(100, $obj->{1});
        $this->assertEquals(200, $obj[2]);
        $this->assertEquals(200, $obj->{2});
    }

    public function testIssetUnset()
    {
        $obj = new \T4\Core\Std();
        $this->assertFalse(isset($obj->foo));
        $obj->foo = 'bar';
        $this->assertTrue(isset($obj->foo));
        unset($obj->foo);
        $this->assertFalse(isset($obj->foo));
    }

    public function testChain()
    {
        $obj = new \T4\Core\Std();
        $this->assertFalse(isset($obj->foo));
        $this->assertFalse(isset($obj->foo->bar));
        $this->assertTrue(empty($obj->foo));
        $this->assertTrue(empty($obj->foo->bar));

        $obj->foo->bar = 'baz';
        $this->assertTrue(isset($obj->foo));
        $this->assertTrue(isset($obj->foo->bar));
        $this->assertFalse(empty($obj->foo));
        $this->assertFalse(empty($obj->foo->bar));

        $this->assertTrue($obj->foo instanceof T4\Core\Std);
        $this->assertEquals(new \T4\Core\Std(['bar' => 'baz']), $obj->foo);
        $this->assertEquals('baz', $obj->foo->bar);
    }

    public function testValidate()
    {
        $obj = new TestStdClass1();
        $obj->foo = -1;
        $this->assertFalse(isset($obj->foo));
        $obj->foo = 1;
        $this->assertTrue(isset($obj->foo));
        $this->assertEquals(1, $obj->foo);
    }

    public function testSanitize()
    {
        $obj = new TestStdClass2();
        $obj->foo = -1;
        $this->assertEquals(-10, $obj->foo);
        $obj->foo = 1;
        $this->assertEquals(10, $obj->foo);
    }

}