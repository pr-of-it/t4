<?php

namespace T4\Orm;

use T4\Dbal\Connection;
use T4\Console\Application;

abstract class Migration
{

    /**
     * @var Connection
     */
    protected $db;

    final public function __construct()
    {
        $this->setDb('default');
    }

    final public function setDb($db)
    {
        $app = Application::instance();
        if (is_string($db)) {
            $this->db = $app->db->{$db};
        } elseif ($db instanceof Connection) {
            $this->db = $db;
        }

    }

    final public function getName()
    {
        $className = get_class($this);
        preg_match('~\\\\([^\\\\]+?)$~', $className, $m);
        return $m[1];
    }

    final public function getTimestamp()
    {
        preg_match('~m_(\d+)_~', $this->getName(), $m);
        return (int)$m[1];
    }

    final public function beginTransaction() {
        return $this->db->beginTransaction();
    }

    final public function rollbackTransaction() {
        return $this->db->rollbackTransaction();
    }

    final public function commitTransaction() {
        return $this->db->commitTransaction();
    }

    abstract public function up();

    abstract public function down();

    final protected function createTable($tableName, $columns = [], $indexes = [], $extensions = [])
    {
        $driver = $this->db->getDriver();
        $driver->createTable($this->db, $tableName, $columns, $indexes, $extensions);
        echo 'Table `' . $tableName . '` is created' . "\n";
    }

    final protected function existsTable($tableName)
    {
        $driver = $this->db->getDriver();
        return $driver->existsTable($this->db, $tableName);
    }

    final protected function renameTable($tableName, $tableNewName)
    {
        $driver = $this->db->getDriver();
        $driver->renameTable($this->db, $tableName, $tableNewName);
        echo 'Table `' . $tableName . '` is renamed into `' . $tableNewName . '`' . "\n";
    }

    final protected function truncateTable($tableName)
    {
        $driver = $this->db->getDriver();
        $driver->truncateTable($this->db, $tableName);
        echo 'Table ' . $tableName . ' is truncated' . "\n";
    }

    final protected function dropTable($tableName)
    {
        $driver = $this->db->getDriver();
        $driver->dropTable($this->db, $tableName);
        echo 'Table ' . $tableName . ' is dropped' . "\n";
    }

    final protected function addColumn($tableName, $columns)
    {
        $driver = $this->db->getDriver();
        $driver->addColumn($this->db, $tableName, $columns);
        echo 'Table `' . $tableName . '` is altered: columns `' . implode('`,`', array_keys($columns)) . '` are added' . "\n";
    }

    final protected function dropColumn($tableName, $columns)
    {
        $columns = (array)$columns;
        $driver = $this->db->getDriver();
        $driver->dropColumn($this->db, $tableName, $columns);
        echo 'Table `' . $tableName . '` is altered: columns `' . implode('`,`', $columns) . '` are dropped' . "\n";
    }

    final protected function renameColumn($tableName, $oldName, $newName)
    {
        $driver = $this->db->getDriver();
        $driver->renameColumn($this->db, $tableName, $oldName, $newName);
        echo 'Table `' . $tableName . '` is altered: column `' . $oldName . '` is renamed into `' . $newName . '`' . "\n";
    }

    final protected function addIndex($tableName, $indexes)
    {
        $driver = $this->db->getDriver();
        $driver->addIndex($this->db, $tableName, $indexes);
        echo 'Table `' . $tableName . '` is altered: indexes `' . implode('`,`', array_keys($indexes)) . '` are added' . "\n";
    }

    final protected function dropIndex($tableName, $indexes)
    {
        $indexes = (array)$indexes;
        $driver = $this->db->getDriver();
        $driver->dropIndex($this->db, $tableName, $indexes);
        echo 'Table `' . $tableName . '` is altered: indexes `' . implode('`,`', $indexes) . '` are dropped' . "\n";
    }

    final protected function insert($tableName, $data)
    {
        $driver = $this->db->getDriver();
        $id = $driver->insert($this->db, $tableName, $data);
        echo 'Data into table ' . $tableName . ' is inserted' . "\n";
        return $id;
    }

}