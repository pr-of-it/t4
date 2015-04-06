<?php

require_once realpath(__DIR__ . '/../../framework/boot.php');

class UploaderTest extends PHPUnit_Framework_TestCase
{

    public function testSuggestUploadedFileName()
    {
        $uploader = new \T4\Http\Uploader();
        $reflector = new ReflectionMethod($uploader, 'suggestUploadedFileName');
        $reflector->setAccessible(true);

        $tmpDir = __DIR__ . DS . md5(time());
        \T4\Fs\Helpers::mkDir($tmpDir);

        $this->assertEquals(
            'test',
            $reflector->invokeArgs($uploader, [$tmpDir, 'test'])
        );
        $this->assertEquals(
            'test.html',
            $reflector->invokeArgs($uploader, [$tmpDir, 'test.html'])
        );

        file_put_contents($tmpDir . DS . 'test.html', 'TEST');
        $this->assertEquals(
            'test_1.html',
            $reflector->invokeArgs($uploader, [$tmpDir, 'test.html'])
        );

        file_put_contents($tmpDir . DS . 'test_1.html', 'TEST');
        $this->assertEquals(
            'test_2.html',
            $reflector->invokeArgs($uploader, [$tmpDir, 'test.html'])
        );
        $this->assertEquals(
            'test_2.html',
            $reflector->invokeArgs($uploader, [$tmpDir, 'test_1.html'])
        );

        unlink($tmpDir . DS . 'test.html');
        unlink($tmpDir . DS . 'test_1.html');
        rmdir($tmpDir);

    }

}
 