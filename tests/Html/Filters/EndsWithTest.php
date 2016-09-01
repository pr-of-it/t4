<?php

namespace T4\Tests\Html\Filters;

use T4\Dbal\Connection;
use T4\Html\Filters\EndsWith;

class EndsWithTestTestConnection extends Connection {
    public function __construct()
    {
        $this->pdo = new \PDO('sqlite:example.db');
    }
}

require_once realpath(__DIR__ . '/../../../framework/boot.php');

class EndsWithTest extends \PHPUnit_Framework_TestCase
{

    public function testGetQueryOptions()
    {
        $filter = new EndsWith('foo', 'Bar');

        $this->assertEquals(
            ['where' => "1 AND foo LIKE '%Bar'"],
            $filter->getQueryOptions(new EndsWithTestTestConnection())
        );
        $this->assertEquals(
            ['where' => "first=:first AND foo LIKE '%Bar'", 'order' => 'id'],
            $filter->getQueryOptions(new EndsWithTestTestConnection(),
                [
                    'where' => 'first=:first',
                    'order' => 'id'
                ]
            )
        );
    }

    protected function tearDown()
    {
        @unlink('example.db');
    }

}