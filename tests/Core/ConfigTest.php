<?php

use T4\Core\Config;

require_once realpath(__DIR__ . '/../../framework/boot.php');

class ConfigTest extends \PHPUnit\Framework\TestCase
{

    const TEST_CONFIG_FILE = __DIR__ . DS . 'config.test.php';

    protected function setUp()
    {
        file_put_contents(self::TEST_CONFIG_FILE, <<<FILE
<?php
return [
    'db' => [
        'default' => [
            'driver' => 'mysql',
            'host' => 'localhost'
        ]
    ],
    'name' => 'test',
];
FILE
        );
    }

    /**
     * @expectedException T4\Core\Exception
     */
    public function testInvalidConfig()
    {
        $config = new Config(__DIR__ . DS . 'wrong.name.php');
    }

    public function testLoad()
    {
        $conf1 = new Config(self::TEST_CONFIG_FILE);
        $conf2 = new Config();
        $conf2->load(self::TEST_CONFIG_FILE);
        $this->assertEquals(
            $conf2,
            $conf1
        );
    }

    public function testValidConfig()
    {
        $config = new Config;
        $config->load(self::TEST_CONFIG_FILE);
        $this->assertInstanceOf(
            T4\Core\Std::class,
            $config->db
        );
        $this->assertInstanceOf(
            T4\Core\Std::class,
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

    public function testSave()
    {
        $config = new Config;
        $config->load(self::TEST_CONFIG_FILE);
        $config->db->default = 42;
        $config->save();

        $expected = <<<FILE
<?php

return [
  'db' =>
  [
    'default' => 42,
  ],
  'name' => 'test',
];
FILE;
        $expected = str_replace(["\r\n", "\r", "\n"], '', $expected);

        $this->assertEquals(
            $expected,
            str_replace(["\r\n", "\r", "\n"], '', file_get_contents(self::TEST_CONFIG_FILE))
        );
    }

    protected function tearDown()
    {
        unlink(self::TEST_CONFIG_FILE);
    }

}
 