<?php

use T4\Core\Config;

require_once realpath(__DIR__ . '/../../framework/boot.php');

class ConfigTest extends PHPUnit_Framework_TestCase
{

    /**
     * @expectedException T4\Core\Exception
     */
    public function testInvalidConfig()
    {
        $config = new Config(__DIR__ . DS . 'wrong.name.php');
    }

    public function testLoad()
    {
        $conf1 = new Config(__DIR__ . DS . 'config.test.php');
        $conf2 = new Config();
        $conf2->load(__DIR__ . DS . 'config.test.php');
        $this->assertEquals(
            $conf2,
            $conf1
        );
    }

    public function testValidConfig()
    {
        $config = new Config;
        $config->load(__DIR__ . DS . 'config.test.php');
        $this->assertInstanceOf(
            'T4\Core\Std',
            $config->db
        );
        $this->assertInstanceOf(
            'T4\Core\Std',
            $config
        );
        $this->assertEquals(
            'mysql',
            $config->db->default->driver
        );
        $this->assertEquals(
            'localhost',
            $config->db->default->host
        );
        $this->assertEquals(
            'test',
            $config->name
        );

    }

}
 