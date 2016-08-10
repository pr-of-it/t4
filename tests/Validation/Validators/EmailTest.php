<?php

namespace T4\Tests\Validation\Validators\Std;

use T4\Validation\Validators\Email;

require_once realpath(__DIR__ . '/../../../framework/boot.php');

class EmailTest extends \PHPUnit_Framework_TestCase
{

    public function testPositive()
    {
        $validator = new Email();
        $result = $validator('test@test.com');
        $this->assertTrue($result);
    }

    /**
     * @expectedException \T4\Validation\Exceptions\EmptyValue
     */
    public function testNegative1()
    {
        $validator = new Email();
        $validator('');
    }
    /**
     * @expectedException \T4\Validation\Exceptions\InvalidEmail
     */
    public function testNegative2()
    {
        $validator = new Email();
        $validator('test');
    }

    /**
     * @expectedException \T4\Validation\Exceptions\InvalidEmail
     */
    public function testNegative3()
    {
        $validator = new Email();
        $validator('test@test.com   ');
    }

}