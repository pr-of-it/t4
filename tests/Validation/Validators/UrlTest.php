<?php

namespace T4\Tests\Validation\Validators;

use T4\Validation\Error;
use T4\Validation\Exceptions\EmptyValue;
use T4\Validation\Exceptions\InvalidUrl;
use T4\Validation\Validators\Url;

require_once realpath(__DIR__ . '/../../../framework/boot.php');

class UrlTest extends \PHPUnit\Framework\TestCase
{

    public function testPositive()
    {
        $validator = new Url();
        $result = $validator('http://test.org');
        $this->assertTrue($result);
    }

    public function testNegative1()
    {
        $value = '';
        try {
            $validator = new Url();
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
            $validator = new Url();
            $validator($value);
        } catch (Error $e) {
            $this->assertInstanceOf(InvalidUrl::class, $e);
            $this->assertEquals($value, $e->value);
            return;
        }
        $this->assertTrue(false);
    }

    public function testNegative3()
    {
        $value = '  http://test.org';
        try {
            $validator = new Url();
            $validator($value);
        } catch (Error $e) {
            $this->assertInstanceOf(InvalidUrl::class, $e);
            $this->assertEquals($value, $e->value);
            return;
        }
        $this->assertTrue(false);
    }

}