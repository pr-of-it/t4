<?php
use T4\Core\Config;

require_once realpath(__DIR__ . '/../../framework/boot.php');

class ConfigSaveTest  extends PHPUnit_Framework_TestCase{

    /**
     * @expectedException T4\Core\Exception
     */
    public function testSave(){

        $config = new Config;
        $config->load(__DIR__ . DS . 'config.test.php');
            $config->name='test1';
        $config->save(__DIR__ . DS . 'config.test.php');
    }

} 