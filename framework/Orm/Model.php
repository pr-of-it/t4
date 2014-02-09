<?php

namespace T4\Orm;

use T4\Core\Std;
use T4\Dbal\DriverFactory;

abstract class Model
    extends Std
{

    use TCrud;

    /**
     * Имя поля первичного ключа
     */
    const PK = '__id';

    /**
     * Схема модели
     * db: name of DB connection from application config
     * table: table name
     * colums[] : colums
     * - type*
     * - length
     * @var array
     */
    static protected $schema = [];

    public static function getSchema()
    {
        $schema = static::$schema;
        if (isset($schema['extensions']) && !empty($schema['extensions'])) {
            foreach ( $schema['extensions'] as $extension ) {
                $extensionClassName = '\\T4\\Orm\\Extensions\\'.ucfirst($extension);
                $extension = new $extensionClassName;
                $schema['columns'] = $extension->prepareColumns($schema['columns']);
            }
        }
    }

    /**
     * Имя таблицы в БД, соответствующей данной модели
     * @return string Имя таблицы в БД
     */
    public static function getTableName()
    {
        $schema = static::getSchema();
        if (isset($schema['table']))
            return $schema['table'];
        else {
            $className = explode('\\', get_called_class());
            return strtolower(array_pop($className)) . 's';
        }
    }

    public static function getDbDriver()
    {
        $schema = static::getSchema();
        $dbConnectionName = $schema['db'] ?: 'default';
        $driver = \T4\Mvc\Application::getInstance()->config->db->{$dbConnectionName}->driver;
        return DriverFactory::getDriver($driver);
    }

    public static function getDbConnection()
    {
        $schema = static::getSchema();
        $dbConnectionName = $schema['db'] ?: 'default';
        $connection = \T4\Mvc\Application::getInstance()->db[$dbConnectionName];
        return $connection;
    }

    public static function getColumns() {
        $schema = static::getSchema();
        return $schema['columns'];
    }

}