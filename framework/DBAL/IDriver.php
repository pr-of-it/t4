<?php

namespace T4\Dbal;


interface IDriver
{
    public function createTable(Connection $connection, $tableName, $columns=[], $indexes=[]);

    public function truncateTable(Connection $connection, $tableName);

    public function dropTable(Connection $connection, $tableName);

    public function findAllByColumn($class, $column, $value);

    public function findByColumn($class, $column, $value);

    public function save($model);

    public function delete($model);

}