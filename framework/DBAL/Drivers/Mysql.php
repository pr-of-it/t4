<?php

namespace T4\Dbal\Drivers;


use T4\Core\Collection;
use T4\Dbal\IDriver;

class Mysql
    implements IDriver
{

    protected $types = [
        'pk' => 'bigint(20) unsigned NOT NULL AUTO_INCREMENT',
        'int' => 'int(11) NOT NULL',
        'float' => 'float NOT NULL',
        'string' => 'varchar(255) NOT NULL',
        'text' => '',
        'money' => '',
    ];

    public function convertAbstractType($type, $params=[]) {

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
        $statement = $connection->execute($sql, [':value' => $value]);
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
        $statement = $connection->execute($sql, [':value' => $value]);
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