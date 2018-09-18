<?php

namespace T4\Tests\Validation\Validators;

use T4\Validation\Error;
use T4\Validation\Exceptions\EmptyValue;
use T4\Validation\Exceptions\InvalidEmail;
use T4\Validation\Validators\Email;

require_once realpath(__DIR__ . '/../../../framework/boot.php');

class EmailTest extends \PHPUnit\Framework\TestCase
{

    public function testPositive()
    {
        $validator = new Email();
        $result = $validator('test@test.com');
        $this->assertTrue($result);
    }

    public function testNegative1()
    {
        $value = '';
        try {
            $validator = new Email();
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
            $validator = new Email();
            $validator($value);
        } catch (Error $e) {
            $this->assertInstanceOf(InvalidEmail::class, $e);
            $this->assertEquals($value, $e->value);
            return;
        }
        $this->assertTrue(false);
    }

    public function testNegative3()
    {
        $value = 'test@test.com   ';
        try {
            $validator = new Email();
            $validator($value);
        } catch (Error $e) {
            $this->assertInstanceOf(InvalidEmail::class, $e);
            $this->assertEquals($value, $e->value);
            return;
        }
        $this->assertTrue(false);
    }

}