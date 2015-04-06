<?php

require_once realpath(__DIR__ . '/../../framework/boot.php');

class TestTag extends \T4\Mvc\Tag
{
    public function render()
    {
    }
}

;

class TagTest extends PHPUnit_Framework_TestCase
{

    public function testParseParams()
    {
        $tag = new TestTag();
        $reflector = new ReflectionMethod($tag, 'parseParams');
        $reflector->setAccessible(true);

        $str = '';
        $this->assertEquals(
            new \T4\Core\Std([]),
            $reflector->invoke($tag, $str)
        );

        $str = '  ';
        $this->assertEquals(
            new \T4\Core\Std([]),
            $reflector->invoke($tag, $str)
        );

        $str = 'a=""';
        $this->assertEquals(
            new \T4\Core\Std(['a' => '']),
            $reflector->invoke($tag, $str)
        );

        $str = 'a="1"';
        $this->assertEquals(
            new \T4\Core\Std(['a' => 1]),
            $reflector->invoke($tag, $str)
        );

        $str = 'a="1"  b="2"';
        $this->assertEquals(
            new \T4\Core\Std(['a' => 1, 'b' => 2]),
            $reflector->invoke($tag, $str)
        );

    }

}