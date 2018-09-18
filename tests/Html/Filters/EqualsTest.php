<?php

namespace T4\Tests\Html\Filters;

use T4\Dbal\Connection;
use T4\Dbal\Query;
use T4\Html\Filters\Equals;

class EqualsTestConnection extends Connection {
    public function __construct()
    {
        $this->pdo = new \PDO('sqlite:example.db');
    }
}

require_once realpath(__DIR__ . '/../../../framework/boot.php');

class EqualsTest extends \PHPUnit\Framework\TestCase
{

    public function testModifyQuery()
    {
        $filter = new Equals('foo', 'Bar', ['connection' => new EqualsTestConnection()]);

        $this->assertEquals(
            new Query(['where' => "TRUE AND foo = :foo", 'params' => [':foo' => 'Bar']]),
            $filter->modifyQuery(
                new Query
            )
        );

        $this->assertEquals(
            new Query(['where' => "first=:first AND foo = :foo", 'order' => 'id', 'params' => [':first' => 42, ':foo' => 'Bar']]),
            $filter->modifyQuery(
                (new Query)->where('first=:first')->order('id')->param(':first', 42)
            )
        );
    }

    public function testGetQueryOptions()
    {
        $filter = new Equals('foo', 'Bar', ['connection' => new EqualsTestConnection()]);

        $this->assertEquals(
            ['where' => "TRUE AND foo = :foo", 'params' => [':foo' => 'Bar']],
            $filter->getQueryOptions()
        );
        $this->assertEquals(
            ['where' => "first=:first AND foo = :foo", 'order' => 'id', 'params' => [':foo' => 'Bar']],
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