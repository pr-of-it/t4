<?php

namespace T4\Dbal;

use T4\Orm\Model;

interface IDriver
    extends IDriverQueryBuilder
{

    public function quoteName($name);

    public function createTable(Connection $connection, $tableName, $columns = [], $indexes = [], $extensions = []);

    public function existsTable(Connection $connection, $tableName);

    public function renameTable(Connection $connection, $tableName, $tableNewName);

    public function truncateTable(Connection $connection, $tableName);

    public function dropTable(Connection $connection, $tableName);

    public function addColumn(Connection $connection, $tableName, array $columns);

    public function dropColumn(Connection $connection, $tableName, array $columns);

    public function renameColumn(Connection $connection, $tableName, $oldName, $newName);

    public function addIndex(Connection $connection, $tableName, array $indexes);

    public function dropIndex(Connection $connection, $tableName, array $indexes);

    public function insert(Connection $connection, $tableName, array $data);

    public function findAllByQuery($class, $query, $params = []);

    public function findByQuery($class, $query, $params = []);

    public function findAll($class, $options = []);

    public function findAllByColumn($class, $column, $value, $options = []);

    public function findByColumn($class, $column, $value, $options = []);

    public function countAllByQuery($class, $query, $params = []);

    public function countAll($class, $options = []);

    public function countAllByColumn($class, $column, $value, $options = []);

    public function save(Model $model);

    public function delete(Model $model);

}