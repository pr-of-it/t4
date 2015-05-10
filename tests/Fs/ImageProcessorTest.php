<?php
require_once realpath(__DIR__ . '/../../framework/boot.php');

class ImageProcessorTest
    extends PHPUnit_Framework_TestCase

{

    public function testFilterResize()
    {
        $testImage = new \T4\Fs\ImageProcessor('/test.jpg');
        $testImage->filterResize(150, 100)->save();
        $path = \T4\Fs\Helpers::getRealPath('/test.jpg');
        $testImageSize = getimagesize($path);
        $this->assertEquals(150, $testImageSize[0]);
        $this->assertEquals(100, $testImageSize[1]);
    }

    public function testFilterResizeToHeight()
    {
        $testImage = new \T4\Fs\ImageProcessor('/test.jpg');
        $testImage->filterResizeToHeight(800)->save();
        $path = \T4\Fs\Helpers::getRealPath('/test.jpg');
        $testImageSize = getimagesize($path);
        $this->assertEquals(800, $testImageSize[1]);
    }

    public function testFilterResizeToWidth()
    {
        $testImage = new \T4\Fs\ImageProcessor('/test.jpg');
        $testImage->filterResizeToWidth(1280)->save();
        $path = \T4\Fs\Helpers::getRealPath('/test.jpg');
        $testImageSize = getimagesize($path);
        $this->assertEquals(1280, $testImageSize[0]);
    }

    public function testFilterZoom()
    {
        $testImage = new \T4\Fs\ImageProcessor('/test.jpg');
        $path = \T4\Fs\Helpers::getRealPath('/test.jpg');
        $testImage->beforeImageSize = getimagesize($path);
        $testImage->filterZoom(30)->save();
        $testImageSize = getimagesize($path);
        $testImage->beforeImageSize[0] *= 0.3;
        $testImage->beforeImageSize[1] *= 0.3;
        $this->assertEquals($testImage->beforeImageSize[0], $testImageSize[0], 'Отличается более чем на 1px', 1.0);
        $this->assertEquals($testImage->beforeImageSize[1], $testImageSize[1], 'Отличается более чем на 1px', 1.0);

    }

}