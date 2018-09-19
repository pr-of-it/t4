<?php

namespace T4\Tests\Validation\Validators;

use T4\Validation\Error;
use T4\Validation\Exceptions\EmptyValue;
use T4\Validation\Exceptions\InvalidIpV4;
use T4\Validation\Validators\IpV4;

require_once realpath(__DIR__ . '/../../../framework/boot.php');

class IpV4Test extends \PHPUnit\Framework\TestCase
{

    public function testPositive()
    {
        $validator = new IpV4();
        $result = $validator('8.8.8.8');
        $this->assertTrue($result);
    }

    public function testNegative1()
    {
        $value = '';
        try {
            $validator = new IpV4();
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
        $value = '8.8.8';
        try {
            $validator = new IpV4();
            $validator($value);
        } catch (Error $e) {
            $this->assertInstanceOf(InvalidIpV4::class, $e);
            $this->assertEquals($value, $e->value);
            return;
        }
        $this->assertTrue(false);
    }

    public function testNegative3()
    {
        $value = '300.200.100.50';
        try {
            $validator = new IpV4();
            $validator($value);
        } catch (Error $e) {
            $this->assertInstanceOf(InvalidIpV4::class, $e);
            $this->assertEquals($value, $e->value);
            return;
        }
        $this->assertTrue(false);
    }

}