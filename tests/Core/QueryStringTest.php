<?php

class QueryStringTest extends \PHPUnit\Framework\TestCase
{

    public function testFromString()
    {
        $url = new \T4\Core\QueryString();
        $url->fromString('foo=bar&baz=42');
        $this->assertEquals('bar', $url['foo']);
        $this->assertEquals(42, $url['baz']);
    }

    public function testToString()
    {
        $url = new \T4\Core\QueryString(['foo' => 'bar', 'baz' => 42]);
        $this->assertEquals('foo=bar&baz=42', $url->toString());
    }

    public function testConstructorToString()
    {
        $this->assertEquals('foo=bar&baz=42', new \T4\Core\QueryString(['foo' => 'bar', 'baz' => 42]));
    }
    
}
