<?php

namespace T4\Tests\Orm\Relations;

use PHPUnit_Extensions_Database_DataSet_IDataSet;
use PHPUnit_Extensions_Database_DB_IDatabaseConnection;

require_once realpath(__DIR__ . '/../../../framework/boot.php');

class HasManySaveTest
    extends \PHPUnit_Extensions_Database_TestCase
{

    /**
     * @var \Pdo
     */
    protected $connection;

    protected function getT4ConnectionConfig()
    {
        return new \T4\Core\Config(['driver' => 'mysql', 'host' => '127.0.0.1', 'dbname' => 't4test', 'user' => 'root', 'password' => '']);
    }

    protected function getT4Connection()
    {
        return new \T4\Dbal\Connection($this->getT4ConnectionConfig());
    }

    public function __construct($name = NULL, array $data = array(), $dataName = '')
    {
        $config = $this->getT4ConnectionConfig();
        $this->connection = new \Pdo('mysql:dbname=' . $config->dbname . ';host=' . $config->host . '', $config->user, $config->password);
        parent::__construct($name, $data, $dataName);
    }


    /**
     * Returns the test database connection.
     *
     * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    protected function getConnection()
    {
        return $this->createDefaultDBConnection($this->connection, 'mysql');
    }

    /**
     * Returns the test dataset.
     *
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected function getDataSet()
    {
        // TODO: Implement getDataSet() method.
    }

    protected function setUp()
    {
    }

    protected function tearDown()
    {
    }

    public function testFirst()
    {

    }
}