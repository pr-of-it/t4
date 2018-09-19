<?php

namespace T4\Tests\Html;

use T4\Dbal\Connection;
use T4\Dbal\Query;
use T4\Html\Sorter;

require_once realpath(__DIR__ . '/../../framework/boot.php');

class SorterTestTestConnection extends Connection {
    public function __construct()
    {
        $this->pdo = new \PDO('sqlite:example.db');
    }
}

class SorterTest extends \PHPUnit\Framework\TestCase
{

    public function testModifyQuery()
    {
        $sorter = new Sorter('foo', 'ASC', ['connection' => new SorterTestTestConnection()]);

        $this->assertEquals(
            new Query(['order' => "foo ASC"]),
            $sorter->modifyQuery(
                new Query
            )
        );

        $this->assertEquals(
            new Query(['where' => "bar=:bar", 'order' => "foo ASC", 'params' => [':bar' => 42]]),
            $sorter->modifyQuery(
                (new Query)->where('bar=:bar')->order('id')->param(':bar', 42)
            )
        );
    }

    protected function tearDown()
    {
        @unlink('example.db');
    }

}