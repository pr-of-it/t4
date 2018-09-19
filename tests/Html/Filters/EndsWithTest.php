<?php

namespace T4\Tests\Html\Filters;

use T4\Dbal\Connection;
use T4\Dbal\Query;
use T4\Html\Filters\EndsWith;

class EndsWithTestTestConnection extends Connection {
    public function __construct()
    {
        $this->pdo = new \PDO('sqlite:example.db');
    }
}

require_once realpath(__DIR__ . '/../../../framework/boot.php');

class EndsWithTest extends \PHPUnit\Framework\TestCase
{

    public function testModifyQuery()
    {
        $filter = new EndsWith('foo', 'Bar', ['connection' => new EndsWithTestTestConnection()]);

        $this->assertEquals(
            new Query(['where' => "TRUE AND foo LIKE '%Bar'"]),
            $filter->modifyQuery(
                new Query
            )
        );

        $this->assertEquals(
            new Query(['where' => "first=:first AND foo LIKE '%Bar'", 'order' => 'id']),
            $filter->modifyQuery(
                (new Query)->where('first=:first')->order('id')
            )
        );
    }

    public function testGetQueryOptions()
    {
        $filter = new EndsWith('foo', 'Bar', ['connection' => new EndsWithTestTestConnection()]);

        $this->assertEquals(
            ['where' => "TRUE AND foo LIKE '%Bar'"],
            $filter->getQueryOptions()
        );
        $this->assertEquals(
            ['where' => "first=:first AND foo LIKE '%Bar'", 'order' => 'id'],
            $filter->getQueryOptions(
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