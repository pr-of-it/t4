<?php

namespace T4\Tests\Validation\Validators;

use T4\Validation\Error;
use T4\Validation\Exceptions\EmptyValue;
use T4\Validation\Exceptions\InvalidDateTime;
use T4\Validation\Validators\DateTime;

require_once realpath(__DIR__ . '/../../../framework/boot.php');

class DateTimeTest extends \PHPUnit\Framework\TestCase
{

    public function testPositive()
    {
        $validator = new DateTime();
        $result = $validator('2000-01-01');
        $this->assertTrue($result);
        $result = $validator(new \DateTime('2000-01-01 10:01:01'));
        $this->assertTrue($result);
    }

    public function testNegative1()
    {
        $value = '';
        try {
            $validator = new DateTime();
            $validator($value);
        } catch (Error $e) {
            $this->assertInstanceOf(EmptyValue::class, $e);
            $this->assertEquals($value, $e->value);
            return;
        }
        $this->assertTrue(false);
    }

    public function testNegative2()
    {
        $value = 'test';
        try {
            $validator = new DateTime();
            $validator($value);
        } catch (Error $e) {
            $this->assertInstanceOf(InvalidDateTime::class, $e);
            $this->assertEquals($value, $e->value);
            return;
        }
        $this->assertTrue(false);
    }

    public function testNegative3()
    {
        $value = '2000-13-45 34:56:78';
        try {
            $validator = new DateTime();
            $validator($value);
        } catch (Error $e) {
            $this->assertInstanceOf(InvalidDateTime::class, $e);
            $this->assertEquals($value, $e->value);
            return;
        }
        $this->assertTrue(false);
    }

}