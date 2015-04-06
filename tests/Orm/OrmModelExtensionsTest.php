<?php

namespace {
    require_once realpath(__DIR__ . '/../../framework/boot.php');
}

namespace T4\Orm\Extensions {

    class Foo extends \T4\Orm\Extension
    {
        public function prepareColumns($columns, $class = '')
        {
            return $columns + ['year' => ['type' => 'integer']];
        }

        public function prepareRelations($relations, $class = '')
        {
            return $relations + ['publisher' => ['type' => \T4\Orm\Model::HAS_ONE, 'model' => 'Publisher']];
        }
    }
}

namespace {

    class BookExtTestModel1 extends \T4\Orm\Model
    {
        protected static $schema = [
            'columns' => [
                'title' => ['type' => 'string'],
                'author' => ['type' => 'string', 'length' => 100],
            ],
        ];
        protected static $extensions = ['foo'];
    }

    class OrmModelExtensionsTest extends PHPUnit_Framework_TestCase
    {

        public function testColumns()
        {
            $this->assertEquals(
                [
                    'title' => ['type' => 'string'],
                    'author' => ['type' => 'string', 'length' => 100],
                    'year' => ['type' => 'integer'],
                ],
                \BookExtTestModel1::getColumns()
            );
        }

        public function testRelations()
        {
            $this->assertEquals(
                [
                    'publisher' => ['type' => \T4\Orm\Model::HAS_ONE, 'model' => 'Publisher']
                ],
                \BookExtTestModel1::getRelations()
            );
        }

    }
}