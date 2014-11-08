<?php

abstract class DbTestClass
    extends PHPUnit_Extensions_Database_TestCase
{
    protected $connection;

    public function __construct()
    {
        $config = $this->getT4ConnectionConfig();
        $this->connection = new \Pdo('mysql:dbname=' . $config->dbname . ';host=' . $config->host . '', $config->user, $config->password);
        $this->connection->query('DROP TABLE `comments`');

        $migration = new mTestMigration();
        $migration->setDb($this->getT4Connection());
        $migration->up();
    }

    function setUp()
    {
        $this->connection->query('TRUNCATE TABLE `comments`');
        CommentTestModel::setConnection($this->getT4Connection());
    }

    protected function getT4ConnectionConfig()
    {
        return new \T4\Core\Std(['driver' => 'mysql', 'host' => '127.0.0.1', 'dbname' => 't4test', 'user' => 'root', 'password' => '']);
    }

    protected function getT4Connection()
    {
        return new \T4\Dbal\Connection($this->getT4ConnectionConfig());
    }

    protected function _testDbElement($id, $lft, $rgt, $lvl, $prt)
    {
        $res = $this->getT4Connection()->query("SELECT * FROM `comments` WHERE `__id`=:id", [':id'=>$id])->fetch();
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
        return $this->createDefaultDBConnection($this->connection, 'mysql');
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