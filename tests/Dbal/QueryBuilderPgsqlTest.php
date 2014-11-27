<?php

require_once realpath(__DIR__ . '/../../framework/boot.php');

class QueryBuilderPgsqlTest extends PHPUnit_Framework_TestCase {

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
    }

    public function testPgslqMakeInsertQuery()
    {
        $builder = new \T4\Dbal\QueryBuilder();
        $query = $builder->insert('test')->values(['foo' => ':foo', 'bar' => ':bar'])->getQuery('pgsql');
        $this->assertEquals("INSERT INTO \"test\"\n(\"foo\", \"bar\")\nVALUES (:foo, :bar)", $query);
    }

    public function testPgslqMakeDeleteQuery()
    {
        $builder = new \T4\Dbal\QueryBuilder();
        $query = $builder->delete('test1, test2')->where('foo=:foo')->getQuery('pgsql');
        $this->assertEquals("DELETE FROM \"test1\" AS t1, \"test2\" AS t2\nWHERE foo=:foo", $query);
    }

}