<?php

require_once realpath(__DIR__ . '/../../framework/boot.php');

class BookTestModel1 extends \T4\Orm\Model
{
    protected static $schema = [
        'columns' => [
            'title' => ['type' => 'string'],
            'author' => ['type' => 'string', 'length' => 100],
        ],
    ];
}

class BookTestModel2 extends \T4\Orm\Model
{
    protected static $schema = [
        'table' => 'books',
        'columns' => [
            'title' => ['type' => 'string'],
            'author' => ['type' => 'string', 'length' => 200],
        ],
    ];
}

class BookTestModel3 extends \T4\Orm\Model
{
    protected static $schema = [
        'db' => 'test',
        'table' => 'books',
        'columns' => [
            'title' => ['type' => 'string'],
            'author' => ['type' => 'string', 'length' => 200],
        ],
        'relations' => [
            'publisher' => ['type' => self::HAS_ONE, 'model' => 'Publisher']
        ],
    ];
}

class BookTestModel4 extends \T4\Orm\Model
{
    protected static $schema = [
        'table' => 'books',
        'columns' => [
            'title' => ['type' => 'string'],
            'author' => ['type' => 'string', 'length' => 200],
        ],
        'relations' => [
            'publisher' => ['type' => self::HAS_ONE, 'model' => 'Publisher']
        ],
    ];

    protected static $extensions = ['test'];
}

class OrmModelSchemaTest extends PHPUnit_Framework_TestCase
{

    public function testSchema()
    {
        $schema = BookTestModel1::getSchema();
        $this->assertEquals([
            'columns' => [
                'title' => ['type' => 'string'],
                'author' => ['type' => 'string', 'length' => 100],
            ],
            'relations' => []
        ], $schema);

        $schema = BookTestModel2::getSchema();
        $this->assertEquals([
            'table' => 'books',
            'columns' => [
                'title' => ['type' => 'string'],
                'author' => ['type' => 'string', 'length' => 200],
            ],
            'relations' => []
        ], $schema);

        $schema = BookTestModel3::getSchema();
        $this->assertEquals([
            'db' => 'test',
            'table' => 'books',
            'columns' => [
                'title' => ['type' => 'string'],
                'author' => ['type' => 'string', 'length' => 200],
            ],
            'relations' => [
                'publisher' => ['type' => \T4\Orm\Model::HAS_ONE, 'model' => 'Publisher']
            ]
        ], $schema);

        $schema = BookTestModel4::getSchema();
        $this->assertEquals([
            'table' => 'books',
            'columns' => [
                'title' => ['type' => 'string'],
                'author' => ['type' => 'string', 'length' => 200],
            ],
            'relations' => [
                'publisher' => ['type' => \T4\Orm\Model::HAS_ONE, 'model' => 'Publisher']
            ]
        ], $schema);
    }

    public function testExtensions()
    {
        $this->assertEquals(['standard'], BookTestModel1::getExtensions());
        $this->assertEquals(['standard'], BookTestModel2::getExtensions());
        $this->assertEquals(['standard'], BookTestModel3::getExtensions());
        $this->assertEquals(['standard', 'test'], BookTestModel4::getExtensions());
    }

    public function testColumns()
    {
        $columns = BookTestModel1::getColumns();
        $this->assertEquals([
            'title' => ['type' => 'string'],
            'author' => ['type' => 'string', 'length' => 100],
        ], $columns);

        $columns = BookTestModel2::getColumns();
        $this->assertEquals([
            'title' => ['type' => 'string'],
            'author' => ['type' => 'string', 'length' => 200],
        ], $columns);

    }

    public function testTableName()
    {
        $this->assertEquals('booktestmodel1s', BookTestModel1::getTableName());
        $this->assertEquals('books', BookTestModel2::getTableName());
    }

    public function testDbConnectionName()
    {
        $this->assertEquals('default', BookTestModel1::getDbConnectionName());
        $this->assertEquals('test', BookTestModel3::getDbConnectionName());
    }

}