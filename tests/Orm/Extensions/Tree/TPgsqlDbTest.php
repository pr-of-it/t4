<?php

trait TPgsqlDbTest
{
    protected $connection;

    public function setUp()
    {
        $config = $this->getT4ConnectionConfig();
        $this->connection = new \Pdo('pgsql:dbname=' . $config->dbname . ';host=' . $config->host . '', $config->user, $config->password);
        $this->connection->query('DROP TABLE comments');
        $this->connection->query('DROP TABLE __migrations');

        $migration = new mTestMigration();
        $migration->setDb($this->getT4Connection());
        $migration->up();

        CommentTestModel::setConnection($this->getT4Connection());
    }

    protected function getT4ConnectionConfig()
    {
        return new \T4\Core\Std(['driver' => 'pgsql', 'host' => '127.0.0.1', 'dbname' => 't4test', 'user' => 'postgres', 'password' => 'postgres']);
    }

    protected function getT4Connection()
    {
        return new \T4\Dbal\Connection($this->getT4ConnectionConfig());
    }

    protected function _testDbElement($id, $lft, $rgt, $lvl, $prt)
    {
        $query = new \T4\Dbal\QueryBuilder();
        $query->select('*')->from('comments')->where('__id=:id')->params([':id'=>$id]);

        $res = $this->getT4Connection()->query($query)->fetch();

        $this->assertEquals($lft, $res['__lft']);
        $this->assertEquals($rgt, $res['__rgt']);
        $this->assertEquals($lvl, $res['__lvl']);
        $this->assertEquals($prt, $res['__prt']);
    }

    /**
     * Returns the test database connection.
     * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    protected function getConnection()
    {
        return $this->createDefaultDBConnection($this->connection, 'pgsql');
    }

    /**
     * Returns the test dataset.
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected function getDataSet()
    {
        return $this->createXMLDataSet(__DIR__ . '/OrmExtTreeTest.data.xml');
    }

} 