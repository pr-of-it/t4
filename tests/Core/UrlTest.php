<?php

require_once realpath(__DIR__ . '/../../framework/boot.php');

class UrlTest extends PHPUnit_Framework_TestCase
{

    public function testFromString()
    {
        $url = new \T4\Core\Url();
        $url->fromString('http://test.local:80/path/to/file?arg1=val1&arg2=val2#frg');
        $this->assertEquals('http', $url->protocol);
        $this->assertEquals('test.local', $url->host);
        $this->assertEquals(80, $url->port);
        $this->assertEquals('/path/to/file', $url->path);
        $this->assertEquals('arg1=val1&arg2=val2', $url->query);
        $this->assertEquals('frg', $url->fragment);

        $url = new \T4\Core\Url();
        $url->fromString('test.local/path/to/file?arg1=val1&arg2=val2#frg');
        $this->assertNull($url->protocol);
        $this->assertEquals('test.local', $url->host);
        $this->assertNull($url->port);
        $this->assertEquals('/path/to/file', $url->path);
        $this->assertEquals('arg1=val1&arg2=val2', $url->query);
        $this->assertEquals('frg', $url->fragment);

        $url = new \T4\Core\Url();
        $url->fromString('/path/to/file?arg1=val1&arg2=val2#frg');
        $this->assertNull($url->protocol);
        $this->assertNull($url->host);
        $this->assertNull($url->port);
        $this->assertEquals('/path/to/file', $url->path);
        $this->assertEquals('arg1=val1&arg2=val2', $url->query);
        $this->assertEquals('frg', $url->fragment);

        $url = new \T4\Core\Url();
        $url->fromString('/path/to/file?arg1=val1&arg2=val2');
        $this->assertNull($url->protocol);
        $this->assertNull($url->host);
        $this->assertNull($url->port);
        $this->assertEquals('/path/to/file', $url->path);
        $this->assertEquals('arg1=val1&arg2=val2', $url->query);
        $this->assertNull($url->fragment);

        $url = new \T4\Core\Url();
        $url->fromString('/path/to/file#frg');
        $this->assertNull($url->protocol);
        $this->assertNull($url->host);
        $this->assertNull($url->port);
        $this->assertEquals('/path/to/file', $url->path);
        $this->assertNull($url->query);
        $this->assertEquals('frg', $url->fragment);

        $url = new \T4\Core\Url();
        $url->fromString('/path/to/file');
        $this->assertNull($url->protocol);
        $this->assertNull($url->host);
        $this->assertNull($url->port);
        $this->assertEquals('/path/to/file', $url->path);
        $this->assertNull($url->query);
        $this->assertNull($url->fragment);
    }

    public function testToString()
    {
        $url = 'http://test.local:80/path/to/file?arg1=val1&arg2=val2#frg';
        $this->assertEquals($url, (new \T4\Core\Url($url))->toString());
        $url = 'test.local/path/to/file?arg1=val1&arg2=val2#frg';
        $this->assertEquals($url, (new \T4\Core\Url($url))->toString());
        $url = '/path/to/file?arg1=val1&arg2=val2#frg';
        $this->assertEquals($url, (new \T4\Core\Url($url))->toString());
        $url = '/path/to/file?arg1=val1&arg2=val2';
        $this->assertEquals($url, (new \T4\Core\Url($url))->toString());
        $url = '/path/to/file#frg';
        $this->assertEquals($url, (new \T4\Core\Url($url))->toString());
        $url = '/path/to/file';
        $this->assertEquals($url, (new \T4\Core\Url($url))->toString());
    }

}
 