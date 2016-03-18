<?php

require_once realpath(__DIR__ . '/../../framework/boot.php');

class Values extends \T4\Core\Std
{

    protected function validateFoo1($value)
    {
        return true;
    }

    protected function validateFoo2($value)
    {
        return false;
    }

    protected function validateFoo3($value)
    {
        if (strlen($value) < 3) {
            return false;
        } else {
            return true;
        }
    }

    protected function validateFoo4($value)
    {
        if (empty($value)) {
            throw new \T4\Core\Exception('Empty!');
        }
        if (strlen($value) < 3) {
            throw new \T4\Core\Exception('Too short!');
        }
        return true;
    }

    protected function validateFoo5($value)
    {
        if (empty($value)) {
            throw new \T4\Core\Exception('Empty!');
        }
        if (strlen($value) < 3) {
            throw new \T4\Core\Exception('Too short!');
        }
        return true;
    }

    protected function validateFoo6($value)
    {
        if (empty($value)) {
            yield new \T4\Core\Exception('Empty!');
        }
        if (strlen($value) < 3) {
            yield new \T4\Core\Exception('Too short!');
        }
        return true;
    }

    protected function validateFoo7($value)
    {
        if (empty($value)) {
            yield new \T4\Core\Exception('Empty!');
        }
        if (strlen($value) < 3) {
            yield new \T4\Core\Exception('Too short!');
        }
        return true;
    }

}

class ValidateTest extends PHPUnit_Framework_TestCase
{

    public function testValidateTrue()
    {
        $values = new Values();
        $values->foo1 = 'bar';
        $this->assertEquals('bar', $values->foo1);
    }

    public function testValidateFalse()
    {
        $values = new Values();
        $values->foo2 = 'bar';
        $this->assertFalse(isset($values->foo2));
        $this->assertNull($values->foo2);
    }

    public function testValidateTrueFalse()
    {
        $values1 = new Values();
        $values1->foo3 = '1';
        $this->assertFalse(isset($values1->foo3));
        $this->assertNull($values1->foo3);

        $values2 = new Values();
        $values2->foo3 = '123';
        $this->assertTrue(isset($values2->foo3));
        $this->assertEquals('123', $values2->foo3);
    }

    public function testValidateSingleColumnSingleException()
    {
        $values = new Values();

        try {
            $values->foo4 = '';
            $this->assertTrue(false);
        } catch (\T4\Core\Exception $e) {
            $this->assertEquals('Empty!', $e->getMessage());
        }
        $this->assertFalse(isset($values->foo4));

        try {
            $values->foo4 = '1';
            $this->assertTrue(false);
        } catch (\T4\Core\Exception $e) {
            $this->assertEquals('Too short!', $e->getMessage());
        }
        $this->assertFalse(isset($values->foo4));

        $values->foo4 = '123';
        $this->assertTrue(isset($values->foo4));
        $this->assertEquals('123', $values->foo4);
    }

    public function testValidateMultiColumnSingleException()
    {
        $values = new Values();
        try {
            $values->fill(['foo4' => '', 'foo5' => '1']);
            $this->assertTrue(false);
        } catch (\T4\Core\MultiException $e) {
            $this->assertCount(2, $e);
            $this->assertEquals('Empty!', $e[0]->getMessage());
            $this->assertEquals('Too short!', $e[1]->getMessage());
        }
    }

    public function testValidateMultiColumnMultiException()
    {
        $values = new Values();
        try {
            $values->fill(['foo6' => '', 'foo7' => '1']);
            $this->assertTrue(false);
        } catch (\T4\Core\MultiException $e) {
            $this->assertCount(3, $e);
            $this->assertEquals('Empty!', $e[0]->getMessage());
            $this->assertEquals('Too short!', $e[1]->getMessage());
            $this->assertEquals('Too short!', $e[2]->getMessage());
        }
    }

}