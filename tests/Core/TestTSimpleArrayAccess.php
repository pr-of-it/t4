<?php

require_once realpath(__DIR__ . '/../../framework/boot.php');

class TestAA
    implements ArrayAccess, Countable
{
    use \T4\Core\TSimpleArrayAccess;
}

class TestTSimpleArrayAccess extends PHPUnit_Framework_TestCase
{

    public function testCount()
    {
        $arr = new TestAA();
        $this->assertEquals(0, $arr->count());
        $this->assertEquals(0, count($arr));

        $arr[] = 42;

        $this->assertEquals(1, $arr->count());
        $this->assertEquals(1, count($arr));

        unset($arr[0]);
        $this->assertEquals(0, $arr->count());
        $this->assertEquals(0, count($arr));
    }

}
