<?php

namespace T4\Tests\Validation\Validators;

use T4\Validation\Error;
use T4\Validation\Exceptions\InvalidInt;
use T4\Validation\Exceptions\OutOfRange;
use T4\Validation\Validators\IntNum;

require_once realpath(__DIR__ . '/../../../framework/boot.php');

class IntNumTest extends \PHPUnit\Framework\TestCase
{

    public function testPositive()
    {
        $validator = new IntNum();
        $result = $validator(0);
        $this->assertTrue($result);

        $validator = new IntNum();
        $result = $validator(13);
        $this->assertTrue($result);

        $validator = new IntNum(1, 10);
        $result = $validator(7);
        $this->assertTrue($result);
    }

    public function testNegative1()
    {
        $value = '';
        try {
            $validator = new IntNum();
            $validator($value);
        } catch (Error $e) {
            $this->assertInstanceOf(InvalidInt::class, $e);
            $this->assertEquals($value, $e->value);
            return;
        }
        $this->assertTrue(false);
    }

    public function testNegative2()
    {
        $value = '1*2';
        try {
            $validator = new IntNum();
            $validator($value);
        } catch (Error $e) {
            $this->assertInstanceOf(InvalidInt::class, $e);
            $this->assertEquals($value, $e->value);
            return;
        }
        $this->assertTrue(false);
    }

    public function testNegative3()
    {
        $value = 13;
        try {
            $validator = new IntNum(1, 10);
            $validator($value);
        } catch (Error $e) {
            $this->assertInstanceOf(OutOfRange::class, $e);
            $this->assertEquals($value, $e->value);
            return;
        }
        $this->assertTrue(false);
    }

}