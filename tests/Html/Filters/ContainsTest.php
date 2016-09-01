<?php

namespace T4\Tests\Html\Filters;

use T4\Dbal\Connection;
use T4\Html\Filters\Contains;

class ContainsTestTestConnection extends Connection {
    public function __construct()
    {
        $this->pdo = new \PDO('sqlite:example.db');
    }
}

require_once realpath(__DIR__ . '/../../../framework/boot.php');

class ContainsTest extends \PHPUnit_Framework_TestCase
{

    public function testGetQueryOptions()
    {
        $filter = new Contains('foo', 'Bar');

        $this->assertEquals(
            ['where' => "1 AND foo LIKE '%Bar%'"],
            $filter->getQueryOptions(new ContainsTestTestConnection())
        );
        $this->assertEquals(
            ['where' => "first=:first AND foo LIKE '%Bar%'", 'order' => 'id'],
            $filter->getQueryOptions(new ContainsTestTestConnection(),
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