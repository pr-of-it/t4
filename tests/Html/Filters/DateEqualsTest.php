<?php

namespace T4\Tests\Html\Filters;

use T4\Dbal\Query;
use T4\Html\Filters\BeginsWith;
use T4\Dbal\Connection;
use T4\Html\Filters\DateEquals;

require_once realpath(__DIR__ . '/../../../framework/boot.php');

class DateEqualsTestTestConnection extends Connection {
    public function __construct()
    {
        $this->pdo = new \PDO('sqlite:example.db');
    }
}

class DateEqualsTest extends \PHPUnit\Framework\TestCase
{

    public function testModifyQuery()
    {
        $filter = new DateEquals('foo', 'Bar', ['connection' => new DateEqualsTestTestConnection()]);

        $this->assertEquals(
            new Query(['where' => "TRUE AND CAST(foo AS DATE) = :foo", 'params' => [':foo' => 'Bar']]),
            $filter->modifyQuery(
                new Query
            )
        );

        $this->assertEquals(
            new Query(['where' => "first=:first AND CAST(foo AS DATE) = :foo", 'order' => 'id', 'params' => [':first' => 42, ':foo' => 'Bar']]),
            $filter->modifyQuery(
                (new Query)->where('first=:first')->order('id')->params([':first' => 42])
            )
        );
    }

    protected function tearDown()
    {
        @unlink('example.db');
    }

}