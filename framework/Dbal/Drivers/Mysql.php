<?php

namespace T4\Dbal\Drivers;


use T4\Core\Collection;
use T4\Dbal\Connection;
use T4\Dbal\IDriver;
use T4\Orm\Model;

class Mysql
    implements IDriver
{

    protected function createColumnDDL($options) {
        switch ($options['type']) {
            case 'pk':
                return 'BIGINT UNSIGNED NOT NULL AUTO_INCREMENT';
                break;
            case 'relation':
                return 'BIGINT UNSIGNED NOT NULL DEFAULT \'0\'';
                break;
            case 'int':
                return 'INT(11) NOT NULL';
                break;
            case 'float':
                return 'FLOAT NOT NULL';
                break;
            case 'text':
                return 'TEXT';
                break;
            case 'string':
            default:
                return 'VARCHAR(' . ( isset($options['length']) ? (int)$options['length'] : 255 ) . ') NOT NULL';
                break;
        }
    }

    protected function createIndexDDL($name, $options) {
        if (is_numeric($name))
            $name = implode('_', $options['columns']);
        if ( !isset($options['type']) )
            $options['type'] = '';
        $ddl = '`'.$name.'` (`' . implode('`,`', $options['columns']) . '`)';
        switch ( $options['type'] ) {
            case 'unique':
                return 'UNIQUE INDEX ' . $ddl;
                break;
            default:
                return 'INDEX ' . $ddl;
                break;
        }
    }

    public function createTable(Connection $connection, $tableName, $columns=[], $indexes=[])
    {
        $sql = 'CREATE TABLE `'.$tableName.'`';

        $columnsDDL = [];
        $indexesDDL = [];

        $hasPK = false;
        foreach ( $columns as $name => $options ) {
            $columnsDDL[] = '`'.$name.'` ' . $this->createColumnDDL($options);
            if ('pk' == $options['type']) {
                $indexesDDL[] = 'PRIMARY KEY (`' . $name . '`)';
                $hasPK = true;
            }
        }
        if (!$hasPK) {
            array_unshift($columnsDDL, '`' . Model::PK . '` ' . $this->createColumnDDL(['type'=>'pk']));
            $indexesDDL[] = 'PRIMARY KEY (`' . Model::PK . '`)';
        }

        foreach ( $indexes as $name => $options ) {
            $indexesDDL[] = $this->createIndexDDL($name, $options);
        }

        $sql .= ' ( ' .
            implode(', ', $columnsDDL) . ', ' .
            implode(', ', $indexesDDL) .
        ' )';
        $connection->execute($sql);

    }

    public function addColumn(Connection $connection, $tableName, array $columns)
    {
        $sql = 'ALTER TABLE `'.$tableName.'`';
        $columnsDDL = [];
        foreach ( $columns as $name => $options ) {
            $columnsDDL[] = 'ADD COLUMN `'.$name.'` ' . $this->createColumnDDL($options);
        }
        $sql .= ' ' .
            implode(', ', $columnsDDL) .
        '';
        $connection->execute($sql);
    }

    public function dropColumn(Connection $connection, $tableName, array $columns)
    {
        $sql = 'ALTER TABLE `'.$tableName.'`';
        $columnsDDL = [];
        foreach ( $columns as $name ) {
            $columnsDDL[] = 'DROP COLUMN `'.$name.'`';
        }
        $sql .= ' ' .
            implode(', ', $columnsDDL) .
        '';
        $connection->execute($sql);
    }

    public function addIndex(Connection $connection, $tableName, array $indexes)
    {
        $sql = 'ALTER TABLE `'.$tableName.'`';
        $indexesDDL = [];
        foreach ( $indexes as $name => $options ) {
            $indexesDDL[] = 'ADD ' . $this->createIndexDDL($name, $options);
        }
        $sql .= ' ' .
            implode(', ', $indexesDDL) .
        '';
        $connection->execute($sql);
    }

    public function dropIndex(Connection $connection, $tableName, array $indexes)
    {
        $sql = 'ALTER TABLE `'.$tableName.'`';
        $indexesDDL = [];
        foreach ( $indexes as $name ) {
            $indexesDDL[] = 'DROP INDEX `'.$name.'`';
        }
        $sql .= ' ' .
            implode(', ', $indexesDDL) .
        '';
        $connection->execute($sql);
    }

    public function truncateTable(Connection $connection, $tableName)
    {
        $connection->execute('TRUNCATE TABLE `'.$tableName.'`');
    }

    public function dropTable(Connection $connection, $tableName)
    {
        $connection->execute('DROP TABLE `'.$tableName.'`');
    }

    public function findAllByColumn($class, $column, $value)
    {
        $connection = $class::getDbConnection();
        $sql = '
            SELECT *
            FROM `' . $class::getTableName() . '`
            WHERE
                `' . $column . '`=:value
        ';
        $statement = $connection->query($sql, [':value' => $value]);
        // TODO: изгнать отюда \PDO
        $result = $statement->fetchAll(\PDO::FETCH_CLASS, $class);
        if (!empty($result)) {
            $ret = new Collection($result);
            $ret->setNew(false);
        } else {
            $ret = new Collection();
        }
        return $ret;
    }

    public function findByColumn($class, $column, $value)
    {
        $connection = $class::getDbConnection();
        $sql = '
            SELECT *
            FROM `' . $class::getTableName() . '`
            WHERE
                `' . $column . '`=:value
            LIMIT 1
        ';
        $statement = $connection->query($sql, [':value' => $value]);
        $result = $statement->fetchObject($class);
        if (!empty($result))
            $result->setNew(false);
        return $result;
    }

    public function save($model)
    {

        $class = get_class($model);
        $columns = $class::getColumns();
        $sets = [];
        foreach ($columns as $column => $def) {
            $sets[] = '`' . $column . '`=\'' . $model->{$column} . '\'';
        }

        $connection = $class::getDbConnection();
        if ($model->isNew()) {
            $sql = '
                INSERT INTO `' . $class::getTableName() . '`
                SET ' . implode(', ', $sets) . '
            ';
            $connection->execute($sql);
            $model->{$class::PK} = $connection->lastInsertId();
        } else {
            $sql = '
                UPDATE `' . $class::getTableName() . '`
                SET ' . implode(', ', $sets) . '
                WHERE `' . $class::PK . '`=\'' . $model->{$class::PK} . '\'
            ';
            $connection->execute($sql);
        }

    }

    public function delete($model)
    {

        $class = get_class($model);
        $connection = $class::getDbConnection();

        $sql = '
            DELETE FROM `' . $class::getTableName() . '`
            WHERE `' . $class::PK . '`=\'' . $model->{$class::PK} . '\'
        ';
        $connection->execute($sql);

    }

}