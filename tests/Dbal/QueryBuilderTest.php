<?php

require_once realpath(__DIR__ . '/../../framework/boot.php');

class QueryBuilderTest extends PHPUnit_Framework_TestCase {

    /**
     * @expectedException \T4\Dbal\Exception
     */
    public function testSelectEmptyWhereException()
    {
        $builder = new \T4\Dbal\QueryBuilder();
        $builder->select('*');
        echo $builder->getQuery();
    }

    /**
     * @expectedException \T4\Dbal\Exception
     */
    public function testSelectEmptySelectException()
    {
        $builder = new \T4\Dbal\QueryBuilder();
        $builder->from('test');
        echo $builder->getQuery();
    }

    public function testSelect()
    {

        $builder = new \T4\Dbal\QueryBuilder();
        $builder->select('*')->from('test');
        $this->assertEquals(
            "SELECT *\nFROM `test`\n",
            $builder->getQuery()
        );

        $builder = new \T4\Dbal\QueryBuilder();
        $builder->select('a1, a2')->from('test');
        $this->assertEquals(
            "SELECT `a1`, `a2`\nFROM `test`\n",
            $builder->getQuery()
        );

        $builder = new \T4\Dbal\QueryBuilder();
        $builder->select(['a1', 'a2'])->from('test1, test2');
        $this->assertEquals(
            "SELECT `a1`, `a2`\nFROM `test1`, `test2`\n",
            $builder->getQuery()
        );

        $builder = new \T4\Dbal\QueryBuilder();
        $builder->select(['a1', 'a2'])->from(['test1', 'test2']);
        $this->assertEquals(
            "SELECT `a1`, `a2`\nFROM `test1`, `test2`\n",
            $builder->getQuery()
        );

        $builder = new \T4\Dbal\QueryBuilder();
        $builder->select('*')->from('test')->limit(1);
        $this->assertEquals(
            "SELECT *\nFROM `test`\nLIMIT 1\n",
            $builder->getQuery()
        );

        $builder = new \T4\Dbal\QueryBuilder();
        $builder->select('*')->from('test')->limit('1, 2');
        $this->assertEquals(
            "SELECT *\nFROM `test`\nLIMIT 1, 2\n",
            $builder->getQuery()
        );

        $builder = new \T4\Dbal\QueryBuilder();
        $builder->select('*')->from('test')->limit([1, 2]);
        $this->assertEquals(
            "SELECT *\nFROM `test`\nLIMIT 1, 2\n",
            $builder->getQuery()
        );

        $builder = new \T4\Dbal\QueryBuilder();
        $builder->select('*')->from('test')->where('t1=:t1')->limit([1, 2]);
        $this->assertEquals(
            "SELECT *\nFROM `test`\nWHERE t1=:t1\nLIMIT 1, 2\n",
            $builder->getQuery()
        );

        $builder = new \T4\Dbal\QueryBuilder();
        $builder->select('*')->from('test')->where('t1=:t1')->order('t2 DESC')->limit([1, 2]);
        $this->assertEquals(
            "SELECT *\nFROM `test`\nWHERE t1=:t1\nORDER BY t2 DESC\nLIMIT 1, 2\n",
            $builder->getQuery()
        );

        $builder = new \T4\Dbal\QueryBuilder();
        $builder->select('*')->from('test')->where('t1=:t1')->order('t2 DESC')->limit([1, 2])->params([':t1'=>123]);
        $this->assertEquals(
            "SELECT *\nFROM `test`\nWHERE t1=:t1\nORDER BY t2 DESC\nLIMIT 1, 2\n",
            $builder->getQuery()
        );
        $this->assertEquals(
            [':t1'=>123],
            $builder->getParams()
        );

    }

}
 