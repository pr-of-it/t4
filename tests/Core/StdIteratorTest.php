<?php

require_once realpath(__DIR__ . '/../../framework/boot.php');

class TestStdClass4 extends \T4\Core\Std {

    protected function getFoo()
    {
        return $this->__data['foo'] * 10;
    }

}

class StdIteratorTest extends \PHPUnit\Framework\TestCase
{


    public function testSimple()
    {
        $array1 = ['foo' => 1, 'bar' => 2, 'baz' => 3];
        $obj1 = new \T4\Core\Std($array1);

        $expected = [
            1 => [
                'key' => 'foo',
                'value' => 1,
            ],
            2 => [
                'key' => 'bar',
                'value' => 2,
            ],
            3 => [
                'key' => 'baz',
                'value' => 3,
            ],
        ];

        $i = 1;
        foreach ($obj1 as $key => $value)
        {
            $this->assertEquals($expected[$i]['key'], $key);
            $this->assertEquals($expected[$i]['value'], $value);
            $i++;
        }
    }

    public function testWithGetter()
    {
        $array1 = ['foo' => 1, 'bar' => 2, 'baz' => 3];
        $obj1 = new TestStdClass4($array1);

        $expected = [
            1 => [
                'key' => 'foo',
                'value' => 10,
            ],
            2 => [
                'key' => 'bar',
                'value' => 2,
            ],
            3 => [
                'key' => 'baz',
                'value' => 3,
            ],
        ];

        $i = 1;
        foreach ($obj1 as $key => $value)
        {
            $this->assertEquals($expected[$i]['key'], $key);
            $this->assertEquals($expected[$i]['value'], $value);
            $i++;
        }
    }

}