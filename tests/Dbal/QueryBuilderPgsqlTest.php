<?php

require_once realpath(__DIR__ . '/../../framework/boot.php');

class QueryBuilderPgsqlTest extends PHPUnit_Framework_TestCase
{

    public function testPgslqMakeSelectQuery()
    {
        $builder = new \T4\Dbal\QueryBuilder();
        $query = $builder->select()->from('test')->getQuery('pgsql');
        $this->assertEquals("SELECT *\nFROM \"test\" AS t1", $query);

        $builder = new \T4\Dbal\QueryBuilder();
        $query = $builder->select('t1.a1, t2.a2')->from('test1', 'test2')->where('a1=:a1')->getQuery('pgsql');
        $this->assertEquals("SELECT t1.\"a1\", t2.\"a2\"\nFROM \"test1\" AS t1, \"test2\" AS t2\nWHERE a1=:a1", $query);

        $builder = new \T4\Dbal\QueryBuilder();
        $query = $builder
            ->select('t1.a1, t2.a2')
            ->from('test1', 'test2')
            ->where('a1=:a1')
            ->order('id')
            ->offset(20)
            ->limit(10)
            ->getQuery('pgsql');
        $this->assertEquals("SELECT t1.\"a1\", t2.\"a2\"\nFROM \"test1\" AS t1, \"test2\" AS t2\nWHERE a1=:a1\nORDER BY id\nOFFSET 20\nLIMIT 10", $query);

        $builder = new \T4\Dbal\QueryBuilder();
        $query = $builder
            ->select('t1.a1, j1.a2')
            ->from('test1, test2')
            ->join('foo', 'j1.id=t1.id')
            ->join('bar', 'j2.id<t2.id', 'left')
            ->join('baz', 'j3.id>:baz', 'right')
            ->getQuery('pgsql');
        $this->assertEquals("SELECT t1.\"a1\", j1.\"a2\"\nFROM \"test1\" AS t1, \"test2\" AS t2\nFULL JOIN \"foo\" AS j1 ON j1.id=t1.id\nLEFT JOIN \"bar\" AS j2 ON j2.id<t2.id\nRIGHT JOIN \"baz\" AS j3 ON j3.id>:baz", $query);
    }

    public function testPgslqMakeInsertQuery()
    {
        $builder = new \T4\Dbal\QueryBuilder();
        $query = $builder->insert()->table('test')->values(['foo' => ':foo', 'bar' => ':bar'])->getQuery('pgsql');
        $this->assertEquals("INSERT INTO \"test\"\n(\"foo\", \"bar\")\nVALUES (:foo, :bar)", $query);

        $builder = new \T4\Dbal\QueryBuilder();
        $query = $builder->insert('test')->values(['foo' => ':foo', 'bar' => ':bar'])->getQuery('pgsql');
        $this->assertEquals("INSERT INTO \"test\"\n(\"foo\", \"bar\")\nVALUES (:foo, :bar)", $query);
    }

    public function testPgsqlMakeUpdateQuery()
    {
        $builder = new \T4\Dbal\QueryBuilder();
        $query = $builder->update()->table('test')->values(['foo' => ':foo', 'bar' => ':bar'])->where('id=123')->getQuery('pgsql');
        $this->assertEquals("UPDATE \"test\"\nSET \"foo\"=:foo, \"bar\"=:bar\nWHERE id=123", $query);

        $builder = new \T4\Dbal\QueryBuilder();
        $query = $builder->update('test')->values(['foo' => ':foo', 'bar' => ':bar'])->where('id=123')->getQuery('pgsql');
        $this->assertEquals("UPDATE \"test\"\nSET \"foo\"=:foo, \"bar\"=:bar\nWHERE id=123", $query);

        $builder = new \T4\Dbal\QueryBuilder();
        $query = $builder->update('test')->values(['foo' => ':foo', 'bar' => ':bar'])->where('id=123')->order('id DESC')->limit(1)->getQuery('pgsql');
        $this->assertEquals("UPDATE \"test\"\nSET \"foo\"=:foo, \"bar\"=:bar\nWHERE id=123", $query);
    }

    public function testPgslqMakeDeleteQuery()
    {
        $builder = new \T4\Dbal\QueryBuilder();
        $query = $builder->delete('test1, test2')->where('foo=:foo')->getQuery('pgsql');
        $this->assertEquals("DELETE FROM \"test1\" AS t1, \"test2\" AS t2\nWHERE foo=:foo", $query);
        $builder = new \T4\Dbal\QueryBuilder();
        $query = $builder->delete()->tables('test1, test2')->where('foo=:foo')->getQuery('pgsql');
        $this->assertEquals("DELETE FROM \"test1\" AS t1, \"test2\" AS t2\nWHERE foo=:foo", $query);
    }

}