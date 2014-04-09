<?php

namespace T4\Dbal\Drivers;

use T4\Core\Collection;
use T4\Dbal\Connection;
use T4\Dbal\IDriver;
use T4\Dbal\QueryBuilder;
use T4\Orm\Model;

class Mysql
    implements IDriver
{

    protected function createColumnDDL($options)
    {
        switch ($options['type']) {
            case 'pk':
                return 'BIGINT UNSIGNED NOT NULL AUTO_INCREMENT';
                break;
            case 'relation':
            case 'link':
                return 'BIGINT UNSIGNED NOT NULL DEFAULT \'0\'';
                break;
            case 'int':
                return 'INT(11) NOT NULL';
                break;
            case 'float':
                return 'FLOAT NOT NULL';
                break;
            case 'text':
                $options['length'] = isset($options['length']) ? $options['length'] : '';
                switch (strtolower($options['length'])) {
                    case 'tiny':
                    case 'small':
                        return 'TINYTEXT';
                        break;
                    case 'medium':
                        return 'MEDIUMTEXT';
                        break;
                    case 'long':
                    case 'big':
                        return 'LONGTEXT';
                        break;
                    default:
                        return 'TEXT';
                        break;
                }
                break;
            case 'string':
            default:
                return 'VARCHAR(' . (isset($options['length']) ? (int)$options['length'] : 255) . ') NOT NULL';
                break;
        }
    }

    protected function createIndexDDL($name, $options)
    {
        if (is_numeric($name))
            $name = implode('_', $options['columns']);
        if (!isset($options['type']))
            $options['type'] = '';
        $ddl = '`' . $name . '` (`' . implode('`,`', $options['columns']) . '`)';
        switch ($options['type']) {
            case 'unique':
                return 'UNIQUE INDEX ' . $ddl;
                break;
            default:
                return 'INDEX ' . $ddl;
                break;
        }
    }

    public function createTable(Connection $connection, $tableName, $columns = [], $indexes = [], $extensions = [])
    {

        foreach ($extensions as $extension) {
            $extensionClassName = '\\T4\\Orm\\Extensions\\' . ucfirst($extension);
            $extension = new $extensionClassName;
            $columns = $extension->prepareColumns($columns);
            $indexes = $extension->prepareIndexes($indexes);
        }

        $sql = 'CREATE TABLE `' . $tableName . '`';

        $columnsDDL = [];
        $indexesDDL = [];

        $hasPK = false;
        foreach ($columns as $name => $options) {
            $columnsDDL[] = '`' . $name . '` ' . $this->createColumnDDL($options);
            if ('pk' == $options['type']) {
                $indexesDDL[] = 'PRIMARY KEY (`' . $name . '`)';
                $hasPK = true;
            }
        }
        if (!$hasPK) {
            array_unshift($columnsDDL, '`' . Model::PK . '` ' . $this->createColumnDDL(['type' => 'pk']));
            $indexesDDL[] = 'PRIMARY KEY (`' . Model::PK . '`)';
        }

        foreach ($indexes as $name => $options) {
            $indexesDDL[] = $this->createIndexDDL($name, $options);
        }

        $sql .= ' ( ' .
            implode(', ', $columnsDDL) . ', ' .
            implode(', ', $indexesDDL) .
            ' )';
        $connection->execute($sql);

    }

    public function existsTable(Connection $connection, $tableName)
    {
        $sql = 'SHOW TABLES LIKE \'' . $tableName . '\'';
        $result = $connection->query($sql);
        return 0 != count($result->fetchAll());
    }

    public function renameTable(Connection $connection, $tableName, $tableNewName)
    {
        $sql = 'RENAME TABLE `' . $tableName . '` TO `' . $tableNewName . '`';
        $connection->execute($sql);
    }

    public function truncateTable(Connection $connection, $tableName)
    {
        $connection->execute('TRUNCATE TABLE `' . $tableName . '`');
    }

    public function dropTable(Connection $connection, $tableName)
    {
        $connection->execute('DROP TABLE `' . $tableName . '`');
    }

    public function addColumn(Connection $connection, $tableName, array $columns)
    {
        $sql = 'ALTER TABLE `' . $tableName . '`';
        $columnsDDL = [];
        foreach ($columns as $name => $options) {
            $columnsDDL[] = 'ADD COLUMN `' . $name . '` ' . $this->createColumnDDL($options);
        }
        $sql .= ' ' .
            implode(', ', $columnsDDL) .
            '';
        $connection->execute($sql);
    }

    public function dropColumn(Connection $connection, $tableName, array $columns)
    {
        $sql = 'ALTER TABLE `' . $tableName . '`';
        $columnsDDL = [];
        foreach ($columns as $name) {
            $columnsDDL[] = 'DROP COLUMN `' . $name . '`';
        }
        $sql .= ' ' .
            implode(', ', $columnsDDL) .
            '';
        $connection->execute($sql);
    }

    public function addIndex(Connection $connection, $tableName, array $indexes)
    {
        $sql = 'ALTER TABLE `' . $tableName . '`';
        $indexesDDL = [];
        foreach ($indexes as $name => $options) {
            $indexesDDL[] = 'ADD ' . $this->createIndexDDL($name, $options);
        }
        $sql .= ' ' .
            implode(', ', $indexesDDL) .
            '';
        $connection->execute($sql);
    }

    public function dropIndex(Connection $connection, $tableName, array $indexes)
    {
        $sql = 'ALTER TABLE `' . $tableName . '`';
        $indexesDDL = [];
        foreach ($indexes as $name) {
            $indexesDDL[] = 'DROP INDEX `' . $name . '`';
        }
        $sql .= ' ' .
            implode(', ', $indexesDDL) .
            '';
        $connection->execute($sql);
    }

    public function findAll($class, $options = [])
    {
        $query = new QueryBuilder();
        $query
            ->select('*')
            ->from($class::getTableName())
            ->where(!empty($options['where']) ? $options['where'] : '')
            ->order(!empty($options['order']) ? $options['order'] : '')
            ->params(!empty($options['params']) ? $options['params'] : []);

        $result = $class::getDbConnection()->query($query->getQuery(), $query->getParams())->fetchAll(\PDO::FETCH_CLASS, $class);
        if (!empty($result)) {
            $ret = new Collection($result);
            $ret->setNew(false);
        } else {
            $ret = new Collection();
        }
        return $ret;
    }

    // TODO: полноценная реализация options, сейчас фактически только order
    public function findAllByColumn($class, $column, $value, $options=[])
    {
        $query = new QueryBuilder();
        $query
            ->select('*')
            ->from($class::getTableName())
            ->where('`' . $column . '`=:value')
            ->order(!empty($options['order']) ? $options['order'] : '')
            ->params([':value' => $value]);

        $result = $class::getDbConnection()->query($query->getQuery(), $query->getParams())->fetchAll(\PDO::FETCH_CLASS, $class);
        if (!empty($result)) {
            $ret = new Collection($result);
            $ret->setNew(false);
        } else {
            $ret = new Collection();
        }
        return $ret;
    }

    // TODO: полноценная реализация options, сейчас фактически только order
    public function findByColumn($class, $column, $value, $options=[])
    {
        $query = new QueryBuilder();
        $query
            ->select('*')
            ->from($class::getTableName())
            ->where('`' . $column . '`=:value')
            ->order(!empty($options['order']) ? $options['order'] : '')
            ->limit(1)
            ->params([':value' => $value]);

        $result = $class::getDbConnection()->query($query->getQuery(), $query->getParams())->fetchObject($class);;
        if (!empty($result))
            $result->setNew(false);
        return $result;
    }

    public function save(Model $model)
    {

        $class = get_class($model);
        $columns = $class::getColumns();
        $sets = [];
        foreach ($columns as $column => $def) {
            if (isset($model->{$column})) {
                $sets[] = '`' . $column . '`=\'' . $model->{$column} . '\'';
            } elseif (isset($def['default'])) {
                $sets[] = '`' . $column . '`=\'' . $def['default'] . '\'';
            }
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

    public function delete(Model $model)
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