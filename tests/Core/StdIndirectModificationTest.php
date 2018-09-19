<?php

namespace T4\Tests\Core\Std {

    require_once realpath(__DIR__ . '/../../framework/boot.php');

    class TestStdClass extends \T4\Core\Std {

        public function __construct()
        {
            $this->foo = [];
            $this->bar = 42;
        }
    }

    class StdIndirectModificationTest extends \PHPUnit\Framework\TestCase
    {

        public function testConstruct()
        {
            $obj = new TestStdClass();
            $this->assertEquals([], $obj->foo);
            $this->assertEquals(42, $obj->bar);
        }
    }
}
