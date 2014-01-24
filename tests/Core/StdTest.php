<?php

require_once realpath(__DIR__ . '/../../framework/boot.php');

class StdTest extends PHPUnit_Framework_TestCase {

    public function testElements()
    {
        $obj = new T4\Core\Std();
        $obj->testProp = 'testValue';
        $this->assertEquals('testValue', $obj->testProp);
        $this->assertEquals('testValue', $obj['testProp']);
        $this->assertEquals($obj['testProp'], $obj->testProp);
    }

    public function testNumericOffsets() {
        $obj = new \T4\Core\Std();
        $obj[1] = 100;
        $obj->{2} = 200;
        $this->assertEquals(100, $obj[1]);
        $this->assertEquals(100, $obj->{1});
        $this->assertEquals(200, $obj[2]);
        $this->assertEquals(200, $obj->{2});
    }

    public function testEmpty()
    {
        /*
        $obj = new T4\Core\Std();
        $this->assertTrue($obj->isEmpty());
        $obj->testProp = 123;
        $this->assertFalse($obj->isEmpty());
        */
    }

    public function testChain() {
        $obj = new \T4\Core\Std();
        $obj->propA = 1;
        $this->assertTrue(is_int($obj->propA));
        $obj->propB->propC = 2;
        //$this->assertTrue( $obj->propB instanceof \T4\Core\Std );
        $this->assertTrue(is_int($obj->propB->propC));
    }

    public function testCountable() {
        $obj = new \T4\Core\Std();
        $this->assertEquals(0, count($obj));
        $obj->a = 1;
        $this->assertEquals(1, count($obj));
        $obj->b = 2;
        $this->assertEquals(2, count($obj));
        unset($obj->a);
        $this->assertEquals(1, count($obj));
    }

    public function testArrayable() {
        $obj = new \T4\Core\Std();
        $obj->a = 1;
        $this->assertEquals(['a'=>1], $obj->toArray());
    }

}