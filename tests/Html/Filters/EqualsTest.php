<?php

namespace T4\Tests\Html\Filters;

use T4\Dbal\Connection;
use T4\Html\Filters\Contains;
use T4\Html\Filters\Equals;

class EqualsTestConnection extends Connection {
    public function __construct()
    {
        $this->pdo = new \PDO('sqlite:example.db');
    }
}

require_once realpath(__DIR__ . '/../../../framework/boot.php');

class EqualsTest extends \PHPUnit_Framework_TestCase
{

    public function testGetQueryOptions()
    {
        $filter = new Equals('foo', 'Bar');

        $this->assertEquals(
            ['where' => "1 AND foo = :foo", 'params' => [':foo' => 'Bar']],
            $filter->getQueryOptions(new EqualsTestConnection())
        );
        $this->assertEquals(
            ['where' => "first=:first AND foo = :foo", 'order' => 'id', 'params' => [':foo' => 'Bar']],
            $filter->getQueryOptions(new EqualsTestConnection(),
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