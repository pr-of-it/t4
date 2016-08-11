<?php

namespace T4\Tests\Validation\Validators;

use T4\Validation\Validators\Email;
use T4\Validation\Validators\IntNum;
use T4\Validation\Validators\Url;

require_once realpath(__DIR__ . '/../../../framework/boot.php');

class IntNumTest extends \PHPUnit_Framework_TestCase
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

    /**
     * @expectedException \T4\Validation\Exceptions\InvalidInt
     */
    public function testNegative1()
    {
        $validator = new IntNum();
        $validator('');
    }

    /**
     * @expectedException \T4\Validation\Exceptions\InvalidInt
     */
    public function testNegative2()
    {
        $validator = new IntNum();
        $validator('1*2');
    }

    /**
     * @expectedException \T4\Validation\Exceptions\OutOfRange
     */
    public function testNegative3()
    {
        $validator = new IntNum(1, 10);
        $validator(13);
    }

}