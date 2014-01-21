<?php

namespace T4\Orm;


use T4\Dbal\Connection;
use T4\MVC\Application;

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

    final protected function createTable($tableName, $columns=[], $indexes=[])
    {
        $driver = $this->db->getDriver();
        $driver->createTable($this->db, $tableName, $columns, $indexes);
        echo 'Table `' . $tableName . '` is created'."\n";
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
        echo 'Table `' . $tableName . '` is altered: columns `'.implode('`,`', array_keys($columns)).'` are dropped'."\n";
    }

}