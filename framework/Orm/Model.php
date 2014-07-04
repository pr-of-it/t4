<?php

namespace T4\Orm;

use T4\Core\Std;
use T4\Dbal\DriverFactory;

abstract class Model
    extends Std
{

    use TMagic, TCrud, TRelations;

    const PK = '__id';

    const HAS_ONE = 'hasOne';
    const BELONGS_TO = 'belongsTo';
    const HAS_MANY = 'hasMany';
    const MANY_TO_MANY = 'manyToMany';

    /**
     * db: name of DB connection from application config
     * table: table name
     * columns[] : columns
     * - type*
     * - length
     * relations[] : relations
     * - type*
     * - model*
     * - on
     * @var array
     */
    static protected $schema = [];

    /**
     * @var array
     */
    static protected $extensions = [];

    /**
     * @return array
     */
    static public function getSchema()
    {
        static $schema = null;
        if (null === $schema) {
            $class = get_called_class();
            $schema = $class::$schema;
            $extensions = $class::getExtensions();
            foreach ( $extensions as $extension ) {
                $extensionClassName = '\\T4\\Orm\\Extensions\\'.ucfirst($extension);
                $extension = new $extensionClassName;
                $schema['columns'] = $extension->prepareColumns($schema['columns'], $class);
                $schema['relations'] = $extension->prepareRelations(isset($schema['relations']) ? $schema['relations'] : [], $class);
            }
        }
        return $schema;
    }

    /**
     * @return array
     */
    static public function getColumns() {
        $schema = static::getSchema();
        return $schema['columns'];
    }

    /**
     * @return array
     */
    static public function getExtensions()
    {
        return !empty(static::$extensions) ?
            array_merge(['standard'], static::$extensions) :
            ['standard'];
    }

    /**
     * @return string
     */
    static public function getTableName()
    {
        $schema = static::getSchema();
        if (isset($schema['table']))
            return $schema['table'];
        else {
            $className = explode('\\', get_called_class());
            return strtolower(array_pop($className)) . 's';
        }
    }

    /**
     * @return string
     */
    static public function getDbConnectionName()
    {
        $schema = static::getSchema();
        return !empty($schema['db']) ? $schema['db'] : 'default';
    }

    /**
     * @return \T4\Dbal\Connection
     */
    static public function getDbConnection()
    {
        if ('cli' == PHP_SAPI) {
            $app = \T4\Console\Application::getInstance();
        } else {
            $app = \T4\Mvc\Application::getInstance();
        }
        $connection = $app->db->{static::getDbConnectionName()};
        return $connection;
    }

    /**
     * @return \T4\Dbal\IDriver
     */
    static public function getDbDriver()
    {
        if ('cli' == PHP_SAPI) {
            $app = \T4\Console\Application::getInstance();
        } else {
            $app = \T4\Mvc\Application::getInstance();
        }
        $driverName = $app->config->db->{static::getDbConnectionName()}->driver;
        return DriverFactory::getDriver($driverName);
    }

}