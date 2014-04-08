<?php

namespace T4\Orm;

use T4\Dbal\Connection;
use T4\Mvc\Application;

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
        $app = Application::getInstance();
        $this->db = $app->db->{$db};
    }

    final public function getName() {
        $className = get_class($this);
        preg_match('~\\\\([^\\\\]+?)$~', $className, $m);
        return $m[1];
    }

    final public function getTimestamp() {
        preg_match('~m_(\d+)_~', $this->getName(), $m);
        return (int)$m[1];
    }

    abstract public function up();

    abstract public function down();

    final protected function createTable($tableName, $columns=[], $indexes=[], $extensions=[])
    {
        $driver = $this->db->getDriver();
        $driver->createTable($this->db, $tableName, $columns, $indexes, $extensions);
        echo 'Table `' . $tableName . '` is created'."\n";
    }

    final protected function renameTable($tableName, $tableNewName)
    {
        $driver = $this->db->getDriver();
        $driver->renameTable($this->db, $tableName, $tableNewName);
        echo 'Table `' . $tableName . '` is renamed into `' . $tableNewName . '`'."\n";
    }

    final protected function truncateTable($tableName)
    {
        $driver = $this->db->getDriver();
        $driver->truncateTable($this->db, $tableName);
        echo 'Table ' . $tableName . ' is truncated'."\n";
    }

    final protected function dropTable($tableName)
    {
        $driver = $this->db->getDriver();
        $driver->dropTable($this->db, $tableName);
        echo 'Table ' . $tableName . ' is dropped'."\n";
    }

    final protected function addColumn($tableName, $columns)
    {
        $driver = $this->db->getDriver();
        $driver->addColumn($this->db, $tableName, $columns);
        echo 'Table `' . $tableName . '` is altered: columns `'.implode('`,`', array_keys($columns)).'` are added'."\n";
    }

    final protected function dropColumn($tableName, $columns)
    {
        $columns = (array)$columns;
        $driver = $this->db->getDriver();
        $driver->dropColumn($this->db, $tableName, $columns);
        echo 'Table `' . $tableName . '` is altered: columns `'.implode('`,`', $columns).'` are dropped'."\n";
    }

    final protected function addIndex($tableName, $indexes)
    {
        $driver = $this->db->getDriver();
        $driver->addIndex($this->db, $tableName, $indexes);
        echo 'Table `' . $tableName . '` is altered: indexes `'.implode('`,`', array_keys($indexes)).'` are added'."\n";
    }

    final protected function dropIndex($tableName, $indexes)
    {
        $indexes = (array)$indexes;
        $driver = $this->db->getDriver();
        $driver->dropIndex($this->db, $tableName, $indexes);
        echo 'Table `' . $tableName . '` is altered: indexes `'.implode('`,`', $indexes).'` are dropped'."\n";
    }

}