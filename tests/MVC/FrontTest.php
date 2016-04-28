<?php

require_once realpath(__DIR__ . '/../../framework/boot.php');

class TestApp implements \T4\Mvc\IApplication {
    public function getModulePath($module = null)
    {
    }
    public function getControllerTemplatesPath($module = null, $controller)
    {
    }
    public function existsModule($module = null)
    {
    }
    public function getRouteConfigPath()
    {
    }
    public function getPath()
    {
    }
    public function existsController($module = null, $controller)
    {
    }
    public function setRoutes(\T4\Core\Config $config = null)
    {
    }
    public function getRouter() : \T4\Mvc\IRouter
    {
    }
    public function run()
    {
    }
    public function setConfig(\T4\Core\Config $config = null)
    {
    }
    public function createController($module = null, $controller)
    {
    }
}

class FrontTest extends PHPUnit_Framework_TestCase
{

    public function testTemplateFileName()
    {
        $front = new \T4\Mvc\Front(new TestApp);
        $this->assertEquals(
            'Foo.html',
            $front->getTemplateFileName(new \T4\Mvc\Route(['action' => 'Foo', 'format' => 'html']))
        );
        $this->assertEquals(
            'Bar.xml',
            $front->getTemplateFileName(new \T4\Mvc\Route(['controller' => 'Test', 'action' => 'Bar', 'format' => 'xml']))
        );
    }

    public function testJsonOutput()
    {
        $front = new \T4\Mvc\Front(new TestApp);

        ob_start();
        @$front->output(new \T4\Core\Std(['foo' => 'bar', 'baz' => 42]), 'json');
        $real = ob_get_contents();
        ob_end_clean();

        $this->assertEquals('{"foo":"bar","baz":42}', $real);
    }

}