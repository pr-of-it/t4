<?php
use T4\Core\Config;

require_once realpath(__DIR__ . '/../../framework/boot.php');

class ConfigSaveTest  extends PHPUnit_Framework_TestCase{


    public function testSave()
    {

        $config = new Config;
        $config->load(__DIR__ . DS . 'configsave.test.php');
        $config->app_title = 'Сайт';
        $config->save(__DIR__ . DS . 'configsave.test.php');
        unset($config);

        $config = new Config;
        $config->load(__DIR__ . DS . 'configsave.test.php');

        $this->assertEquals(
            'Сайт',
            $config->app_title
        );
    }

} 