<?php

namespace T4\Tests\Validation\Validators;

use T4\Validation\Validators\Email;
use T4\Validation\Validators\Url;

require_once realpath(__DIR__ . '/../../../framework/boot.php');

class UrlTest extends \PHPUnit_Framework_TestCase
{

    public function testPositive()
    {
        $validator = new Url();
        $result = $validator('http://test.org');
        $this->assertTrue($result);
    }

    /**
     * @expectedException \T4\Validation\Exceptions\EmptyValue
     */
    public function testNegative1()
    {
        $validator = new Url();
        $validator('');
    }
    /**
     * @expectedException \T4\Validation\Exceptions\InvalidUrl
     */
    public function testNegative2()
    {
        $validator = new Url();
        $validator('test');
    }

    /**
     * @expectedException \T4\Validation\Exceptions\InvalidUrl
     */
    public function testNegative3()
    {
        $validator = new Url();
        $validator('  http://test.org');
    }

}