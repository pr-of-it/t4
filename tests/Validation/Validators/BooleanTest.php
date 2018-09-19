<?php

namespace T4\Tests\Validation\Validators;

use T4\Validation\Error;
use T4\Validation\Exceptions\InvalidBoolean;
use T4\Validation\Validators\Boolean;

require_once realpath(__DIR__ . '/../../../framework/boot.php');

class BooleanTest extends \PHPUnit\Framework\TestCase
{

    public function testPositive()
    {
        $validator = new Boolean();

        $result = $validator('');
        $this->assertTrue($result);
        $result = $validator('false');
        $this->assertTrue($result);
        $result = $validator('off');
        $this->assertTrue($result);
        $result = $validator('no');
        $this->assertTrue($result);
        $result = $validator('0');
        $this->assertTrue($result);
        $result = $validator(0);
        $this->assertTrue($result);

        $result = $validator('true');
        $this->assertTrue($result);
        $result = $validator('on');
        $this->assertTrue($result);
        $result = $validator('yes');
        $this->assertTrue($result);
        $result = $validator('1');
        $this->assertTrue($result);
        $result = $validator(1);
        $this->assertTrue($result);
    }

    public function testNegative()
    {
        $value = 'foo';
        try {
            $validator = new Boolean();
            $validator($value);
        } catch (Error $e) {
            $this->assertInstanceOf(InvalidBoolean::class, $e);
            $this->assertEquals($value, $e->value);
            return;
        }
        $this->assertTrue(false);
    }

}