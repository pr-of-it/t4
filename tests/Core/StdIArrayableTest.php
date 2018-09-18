<?php

require_once realpath(__DIR__ . '/../../framework/boot.php');

class TestStdClass3 extends \T4\Core\Std {

    protected function getFoo()
    {
        return $this->__data['foo'] * 10;
    }

}

class StdIArrayableTest extends \PHPUnit\Framework\TestCase
{


    public function testSimple()
    {
        $array1 = ['foo' => 1, 'bar' => 2, 'baz' => 3];
        $obj1 = new \T4\Core\Std($array1);
        $this->assertEquals(1, $obj1->foo);
        $this->assertEquals(2, $obj1->bar);
        $this->assertEquals(3, $obj1->baz);

        $obj2 = new \T4\Core\Std();
        $obj2->fromArray($array1);
        $this->assertEquals(1, $obj2->foo);
        $this->assertEquals(2, $obj2->bar);
        $this->assertEquals(3, $obj2->baz);

        $this->assertEquals($array1, $obj2->toArray());

        $array2 = ['foo' => 1, 'bar' => ['baz' => 11, 'bla' => 12]];

        $obj3 = new \T4\Core\Std($array2);
        $this->assertEquals(1, $obj3->foo);
        $this->assertTrue($obj3->bar instanceof T4\Core\Std);
        $this->assertEquals(11, $obj3->bar->baz);
        $this->assertEquals(12, $obj3->bar->bla);

        $obj4 = new \T4\Core\Std();
        $obj4->foo = 1;
        $obj4->bar->baz = 11;
        $obj4->bar->bla = 12;
        $this->assertEquals($array2, $obj4->toArray());
    }

    public function testWithGetter()
    {
        $array1 = ['foo' => 1, 'bar' => 2, 'baz' => 3];
        $obj1 = new TestStdClass3($array1);
        $this->assertEquals(10, $obj1->foo);
        $this->assertEquals(2, $obj1->bar);
        $this->assertEquals(3, $obj1->baz);

        $obj2 = new TestStdClass3();
        $obj2->foo = 1;
        $obj2->bar = 2;
        $obj2->baz = 3;
        $this->assertEquals(['foo' => 10, 'bar' => 2, 'baz' => 3], $obj2->toArray());
    }

}