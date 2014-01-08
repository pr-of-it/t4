<?php

namespace T4\Dbal\Drivers;


use T4\Core\Collection;

class Mysql
{

    function findAllByColumn($class, $column, $value)
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

    function findByColumn($class, $column, $value)
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
        if ( !empty($result) )
            $result->setNew(false);
        return $result;
    }

}