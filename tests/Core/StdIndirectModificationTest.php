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

    class StdIndirectModificationTest extends \PHPUnit_Framework_TestCase
    {

        public function testConstruct()
        {
            $obj = new TestStdClass();
            $this->assertEquals([], $obj->foo);
            $this->assertEquals(42, $obj->bar);
        }

        public function testMutate()
        {
            /**
             * THIS TEST FAILS!
            $obj = new TestStdClass();
            $obj->foo[] = 'bla';
            $obj->bar += 2;

            $this->assertEquals(['bla'], $obj->foo);
            $this->assertEquals(44, $obj->bar);
             *
             */
        }

    }

}