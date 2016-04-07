<?php

require_once realpath(__DIR__ . '/../../../framework/boot.php');

class PgsqlDriverTest extends PHPUnit_Framework_TestCase {

    public function testQuoteName()
    {
        $driver = new \T4\Dbal\Drivers\Pgsql();
        $reflector = new ReflectionMethod($driver, 'quoteName');
        $reflector->setAccessible(true);

        $this->assertEquals(
            '"test"',
            $reflector->invokeArgs($driver, ['test'])
        );
        $this->assertEquals(
            '"foo"."bar"',
            $reflector->invokeArgs($driver, ['foo.bar'])
        );
        $this->assertEquals(
            't1."foo"',
            $reflector->invokeArgs($driver, ['t1.foo'])
        );
        $this->assertEquals(
            'j1."foo"',
            $reflector->invokeArgs($driver, ['j1.foo'])
        );
        $this->assertEquals(
            '"foo"."bar"."baz"',
            $reflector->invokeArgs($driver, ['foo.bar.baz'])
        );
        $this->assertEquals(
            '"foo".t1."baz"',
            $reflector->invokeArgs($driver, ['foo.t1.baz'])
        );
    }

    public function testCreateColumnDDL()
    {
        $driver = new \T4\Dbal\Drivers\Pgsql();
        $reflector = new ReflectionMethod($driver, 'createColumnDDL');
        $reflector->setAccessible(true);

        $this->assertEquals(
            '"foo" BIGSERIAL PRIMARY KEY',
            $reflector->invokeArgs($driver, ['table', 'foo', ['type' => 'pk']])
        );
        $this->assertEquals(
            '"foo" BIGINT NOT NULL DEFAULT \'0\'',
            $reflector->invokeArgs($driver, ['table', 'foo', ['type' => 'relation']])
        );
        $this->assertEquals(
            '"foo" SERIAL',
            $reflector->invokeArgs($driver, ['table', 'foo', ['type' => 'serial']])
        );
        $this->assertEquals(
            '"foo" BOOLEAN',
            $reflector->invokeArgs($driver, ['table', 'foo', ['type' => 'boolean']])
        );
        $this->assertEquals(
            '"foo" INTEGER',
            $reflector->invokeArgs($driver, ['table', 'foo', ['type' => 'int']])
        );
        $this->assertEquals(
            '"foo" REAL',
            $reflector->invokeArgs($driver, ['table', 'foo', ['type' => 'float']])
        );
        $this->assertEquals(
            '"foo" TEXT',
            $reflector->invokeArgs($driver, ['table', 'foo', ['type' => 'text']])
        );
        $this->assertEquals(
            '"foo" TIMESTAMP',
            $reflector->invokeArgs($driver, ['table', 'foo', ['type' => 'datetime']])
        );
        $this->assertEquals(
            '"foo" DATE',
            $reflector->invokeArgs($driver, ['table', 'foo', ['type' => 'date']])
        );
        $this->assertEquals(
            '"foo" TIME',
            $reflector->invokeArgs($driver, ['table', 'foo', ['type' => 'time']])
        );
        $this->assertEquals(
            '"foo" CHARACTER(255)',
            $reflector->invokeArgs($driver, ['table', 'foo', ['type' => 'char']])
        );
        $this->assertEquals(
            '"foo" CHARACTER(123)',
            $reflector->invokeArgs($driver, ['table', 'foo', ['type' => 'char', 'length' => 123]])
        );
        $this->assertEquals(
            '"foo" VARCHAR',
            $reflector->invokeArgs($driver, ['table', 'foo', ['type' => 'string']])
        );
        $this->assertEquals(
            '"foo" VARCHAR(123)',
            $reflector->invokeArgs($driver, ['table', 'foo', ['type' => 'string', 'length' => 123]])
        );
    }

    public function testCreateIndexDDL()
    {
        $driver = new \T4\Dbal\Drivers\Pgsql();
        $reflector = new ReflectionMethod($driver, 'createIndexDDL');
        $reflector->setAccessible(true);

        $this->assertEquals(
            'INDEX ON "foo" ("bar")',
            $reflector->invokeArgs($driver, ['foo', '', ['columns' => ['bar']]])
        );
        $this->assertEquals(
            'UNIQUE INDEX ON "foo" ("bar", "baz")',
            $reflector->invokeArgs($driver, ['foo', '', ['type'=>'unique', 'columns' => ['bar', 'baz']]])
        );
        $this->assertEquals(
            'UNIQUE INDEX "test" ON "foo" ("bar", "baz") WHERE id>123',
            $reflector->invokeArgs($driver, ['foo', 'test', ['type'=>'unique', 'columns' => ['bar', 'baz'], 'where' => 'id>123']])
        );
    }

    public function testCreateTableDDL()
    {
        $driver = new \T4\Dbal\Drivers\Pgsql();
        $reflector = new ReflectionMethod($driver, 'createTableDDL');
        $reflector->setAccessible(true);

        $this->assertEquals(
            [
                'CREATE TABLE "foo"' . "\n" . '("__id" BIGSERIAL PRIMARY KEY)'
            ],
            $reflector->invokeArgs($driver, ['foo', []])
        );
        $this->assertEquals(
            [
                'CREATE TABLE "foo"' . "\n" . '("__id" BIGSERIAL PRIMARY KEY, "foo" INTEGER, "bar" VARCHAR)'
            ],
            $reflector->invokeArgs($driver, ['foo', ['foo'=>['type'=>'int'], 'bar'=>['type'=>'string']]])
        );
        $this->assertEquals(
            [
                'CREATE TABLE "foo"' . "\n" . '("__id" BIGSERIAL PRIMARY KEY, "lnk" BIGINT NOT NULL DEFAULT \'0\', "foo" INTEGER, "bar" VARCHAR)',
                'CREATE INDEX ON "foo" ("lnk")',
            ],
            $reflector->invokeArgs($driver, ['foo', ['lnk'=>['type'=>'link'], 'foo'=>['type'=>'int'], 'bar'=>['type'=>'string']]])
        );
    }

}