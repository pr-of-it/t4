<?php

use T4\Core\Config;

require_once realpath(__DIR__ . '/../../framework/boot.php');

class ConfigSaveTest extends PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
        file_put_contents(__DIR__ . '/savetest.config.php', <<<'CONFIG'
<?php

return [
    'application' => [
        'name' => 'Test Application'
    ],
];
CONFIG
        );
    }

    public function testSave()
    {
        $config = new Config(__DIR__ . '/savetest.config.php');
        $this->assertEquals('Test Application', $config->application->name);

        $config->foo = 'bar';
        $config->baz = [1, 2, 3];
        $config->songs = ['Hey' => 'Jude', 'I just' => ['call' => ['to' => 'say']]];

        $config->save();

        $expectedText = <<<'CONFIG'
<?php

return [
  'application' =>
  [
    'name' => 'Test Application',
  ],
  'foo' => 'bar',
  'baz' =>
  [
    0 => 1,
    1 => 2,
    2 => 3,
  ],
  'songs' =>
  [
    'Hey' => 'Jude',
    'I just' =>
    [
      'call' =>
      [
        'to' => 'say',
      ],
    ],
  ],
];
CONFIG;
        $this->assertEquals(
            str_replace("\r\n", "\n", $expectedText),
            str_replace("\r\n", "\n", file_get_contents(__DIR__ . '/savetest.config.php'))
        );
    }

    protected function tearDown()
    {
        unlink(__DIR__ . '/savetest.config.php');
    }

} 