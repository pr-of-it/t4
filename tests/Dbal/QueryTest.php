<?php

require_once realpath(__DIR__ . '/../../framework/boot.php');

class QueryTest extends PHPUnit_Framework_TestCase
{

    public function testPrepareNames()
    {
        $query = new \T4\Dbal\Query();
        $reflector = new ReflectionMethod($query, 'prepareNames');
        $reflector->setAccessible(true);

        $this->assertEquals(
            [],
            $reflector->invokeArgs($query, [[]])
        );

        $this->assertEquals(
            ['*'],
            $reflector->invokeArgs($query, [ ['*'] ])
        );

        $this->assertEquals(
            ['foo'],
            $reflector->invokeArgs($query, [ ['foo'] ])
        );
        $this->assertEquals(
            ['foo'],
            $reflector->invokeArgs($query, [ [' `"foo"`  '] ])
        );

        $this->assertEquals(
            ['foo', 'bar'],
            $reflector->invokeArgs($query, [ ['foo, bar'] ])
        );
        $this->assertEquals(
            ['foo', 'bar'],
            $reflector->invokeArgs($query, [ ['foo', 'bar'] ])
        );
        $this->assertEquals(
            ['foo', 'bar'],
            $reflector->invokeArgs($query, [ [' `"foo, bar"`  '] ])
        );
        $this->assertEquals(
            ['foo', 'bar'],
            $reflector->invokeArgs($query, [ [' `"foo', 'bar"`  '] ])
        );
    }

    public function testColumns()
    {
        $query = new \T4\Dbal\Query();
        $q = $query->select();
        $this->assertInstanceOf(\T4\Dbal\Query::class, $q);
        $this->assertEquals($q, $query);
        $this->assertEquals(['*'], $query->columns);
        $this->assertEquals('select', $query->mode);

        $query = new \T4\Dbal\Query();
        $q = $query->select('*');
        $this->assertInstanceOf(\T4\Dbal\Query::class, $q);
        $this->assertEquals($q, $query);
        $this->assertEquals(['*'], $query->columns);
        $this->assertEquals('select', $query->mode);

        $query = new \T4\Dbal\Query();
        $q = $query->select()->column('*');
        $this->assertInstanceOf(\T4\Dbal\Query::class, $q);
        $this->assertEquals($q, $query);
        $this->assertEquals(['*'], $query->columns);
        $this->assertEquals('select', $query->mode);

        $query = new \T4\Dbal\Query();

        $q = $query->select()->column('foo1');
        $this->assertInstanceOf(\T4\Dbal\Query::class, $q);
        $this->assertEquals($q, $query);
        $this->assertEquals(['foo1'], $query->columns);
        $this->assertEquals('select', $query->mode);

        $q = $q->column('bar1');
        $this->assertInstanceOf(\T4\Dbal\Query::class, $q);
        $this->assertEquals($q, $query);
        $this->assertEquals(['foo1', 'bar1'], $query->columns);
        $this->assertEquals('select', $query->mode);
    }

    public function testTablesAndFrom()
    {
        $query = new \T4\Dbal\Query();

        $q = $query->select()->table('foo');
        $this->assertInstanceOf(\T4\Dbal\Query::class, $q);
        $this->assertEquals($q, $query);
        $this->assertEquals('select', $query->mode);
        $this->assertEquals(['*'], $query->columns);
        $this->assertEquals(['foo'], $query->tables);

        $q = $query->select()->table('bar');
        $this->assertInstanceOf(\T4\Dbal\Query::class, $q);
        $this->assertEquals($q, $query);
        $this->assertEquals('select', $query->mode);
        $this->assertEquals(['*'], $query->columns);
        $this->assertEquals(['foo', 'bar'], $query->tables);

        $q = $query->select()->tables('foo, `bar`');
        $this->assertInstanceOf(\T4\Dbal\Query::class, $q);
        $this->assertEquals($q, $query);
        $this->assertEquals('select', $query->mode);
        $this->assertEquals(['*'], $query->columns);
        $this->assertEquals(['foo', 'bar'], $query->tables);

        $q = $query->select()->tables('foo', '`bar`');
        $this->assertInstanceOf(\T4\Dbal\Query::class, $q);
        $this->assertEquals($q, $query);
        $this->assertEquals('select', $query->mode);
        $this->assertEquals(['*'], $query->columns);
        $this->assertEquals(['foo', 'bar'], $query->tables);

        $q = $query->select()->tables(['foo', '`bar`']);
        $this->assertInstanceOf(\T4\Dbal\Query::class, $q);
        $this->assertEquals($q, $query);
        $this->assertEquals('select', $query->mode);
        $this->assertEquals(['*'], $query->columns);
        $this->assertEquals(['foo', 'bar'], $query->tables);

        $q = $query->select()->from('foo', 'bar');
        $this->assertInstanceOf(\T4\Dbal\Query::class, $q);
        $this->assertEquals($q, $query);
        $this->assertEquals('select', $query->mode);
        $this->assertEquals(['*'], $query->columns);
        $this->assertEquals(['foo', 'bar'], $query->tables);
    }

    /*

    public function testAssignJoin()
    {
        $builder = new \T4\Dbal\QueryBuilder();

        $b = $builder->select()->from('test1')->join('test2', 'j1.id=t1.id');
        $this->assertInstanceOf('\T4\Dbal\QueryBuilder', $b);
        $this->assertEquals($b, $builder);
        $this->assertEquals(['test1'], $builder->from);
        $this->assertEquals([['table' => 'test2', 'on' => 'j1.id=t1.id', 'type' => 'full', 'alias' => '']], $builder->joins);
    }

    public function testAssignWhere()
    {
        $builder = new \T4\Dbal\QueryBuilder();

        $b = $builder->select()->from('test')->where('id=:id');
        $this->assertInstanceOf('\T4\Dbal\QueryBuilder', $b);
        $this->assertEquals($b, $builder);
        $this->assertEquals(['*'], $builder->select);
        $this->assertEquals(['test'], $builder->from);
        $this->assertEquals('id=:id', $builder->where);
    }

    public function testAssignOrder()
    {
        $builder = new \T4\Dbal\QueryBuilder();

        $b = $builder->select()->from('test')->where('id=:id')->order('id DESC');
        $this->assertInstanceOf('\T4\Dbal\QueryBuilder', $b);
        $this->assertEquals($b, $builder);
        $this->assertEquals(['*'], $builder->select);
        $this->assertEquals(['test'], $builder->from);
        $this->assertEquals('id=:id', $builder->where);
        $this->assertEquals('id DESC', $builder->order);
    }

    public function testAssignOffsetLimit()
    {
        $builder = new \T4\Dbal\QueryBuilder();

        $b = $builder->offset(0);
        $this->assertInstanceOf('\T4\Dbal\QueryBuilder', $b);
        $this->assertEquals($b, $builder);
        $this->assertEquals(0, $builder->offset);

        $b = $builder->offset(10);
        $this->assertInstanceOf('\T4\Dbal\QueryBuilder', $b);
        $this->assertEquals($b, $builder);
        $this->assertEquals(10, $builder->offset);

        $b = $builder->offset('abcd');
        $this->assertInstanceOf('\T4\Dbal\QueryBuilder', $b);
        $this->assertEquals($b, $builder);
        $this->assertEquals(0, $builder->offset);

        $b = $builder->limit(0);
        $this->assertInstanceOf('\T4\Dbal\QueryBuilder', $b);
        $this->assertEquals($b, $builder);
        $this->assertEquals(0, $builder->limit);

        $b = $builder->limit(10);
        $this->assertInstanceOf('\T4\Dbal\QueryBuilder', $b);
        $this->assertEquals($b, $builder);
        $this->assertEquals(10, $builder->limit);

        $b = $builder->limit('abcd');
        $this->assertInstanceOf('\T4\Dbal\QueryBuilder', $b);
        $this->assertEquals($b, $builder);
        $this->assertEquals(0, $builder->limit);
    }

    public function testAssignInsert()
    {
        $builder = new \T4\Dbal\QueryBuilder();

        $b = $builder->insert('test');
        $this->assertInstanceOf('\T4\Dbal\QueryBuilder', $b);
        $this->assertEquals($b, $builder);
        $this->assertEquals('insert', $builder->mode);
        $this->assertEquals(['test'], $builder->insertTables);

        $b = $builder->values(['foo' => ':foo', 'bar' => ':bar']);
        $this->assertInstanceOf('\T4\Dbal\QueryBuilder', $b);
        $this->assertEquals($b, $builder);
        $this->assertEquals(['foo' => ':foo', 'bar' => ':bar'], $builder->values);
    }

    public function testAssignUpdate()
    {
        $builder = new \T4\Dbal\QueryBuilder();

        $b = $builder->update('test');
        $this->assertInstanceOf('\T4\Dbal\QueryBuilder', $b);
        $this->assertEquals($b, $builder);
        $this->assertEquals('update', $builder->mode);
        $this->assertEquals(['test'], $builder->updateTables);

        $b = $builder->table('test1');
        $this->assertInstanceOf('\T4\Dbal\QueryBuilder', $b);
        $this->assertEquals($b, $builder);
        $this->assertEquals('update', $builder->mode);
        $this->assertEquals(['test', 'test1'], $builder->updateTables);

        $b = $builder->values(['foo' => ':foo', 'bar' => ':bar']);
        $this->assertInstanceOf('\T4\Dbal\QueryBuilder', $b);
        $this->assertEquals($b, $builder);
        $this->assertEquals(['foo' => ':foo', 'bar' => ':bar'], $builder->values);
    }

    public function testAssignDelete()
    {
        $builder = new \T4\Dbal\QueryBuilder();
        $b = $builder->delete('test')->where('foo=:foo');

        $this->assertInstanceOf('\T4\Dbal\QueryBuilder', $b);
        $this->assertEquals($b, $builder);
        $this->assertEquals('delete', $builder->mode);
        $this->assertEquals(['test'], $builder->deleteTables);
        $this->assertEquals('foo=:foo', $builder->where);

        $builder = new \T4\Dbal\QueryBuilder();
        $b = $builder->delete('test1, test2')->where('foo=:foo AND bar<:bar')->order('id')->limit(10);

        $this->assertInstanceOf('\T4\Dbal\QueryBuilder', $b);
        $this->assertEquals($b, $builder);
        $this->assertEquals('delete', $builder->mode);
        $this->assertEquals(['test1', 'test2'], $builder->deleteTables);
        $this->assertEquals('foo=:foo AND bar<:bar', $builder->where);
        $this->assertEquals('id', $builder->order);
        $this->assertEquals(10, $builder->limit);
    }
    */

}