<?php

namespace T4\Tests\Orm\Relations;

use PHPUnit_Extensions_Database_DataSet_IDataSet;
use PHPUnit_Extensions_Database_DB_IDatabaseConnection;
use T4\Dbal\Query;

require_once realpath(__DIR__ . '/../../../framework/boot.php');

abstract class BaseTest
    extends \PHPUnit\DbUnit\TestCase
{

    /**
     * @var \Pdo
     */
    protected $connection;

    protected function getT4ConnectionConfig()
    {
        return new \T4\Core\Config(require __DIR__ . '/../../dbConfigMySql.php');
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
    
    protected function assertSelectAll($table, $assertedData)
    {
        $data =
            $this->getT4Connection()
                ->query(
                    (new Query())->select()->from($table)
                )->fetchAll(\PDO::FETCH_ASSOC);
        $this->assertEquals(count($assertedData), count($data));
        foreach ($data as $i => $row) {
            $this->assertEquals($row, $assertedData[$i]);
        }
    }

}