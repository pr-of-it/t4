<?php

namespace T4\Orm;

use T4\Core\IActiveRecord;
use T4\Core\Std;
use T4\Dbal\Connection;

abstract class Model
    extends Std
    implements IActiveRecord
{

    use TMagic, TActiveRecord, TCrud, TRelations;

    const PK = '__id';

    public function getPk()
    {
        return $this->{static::PK};
    }

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
    protected static $schema = [];

    /**
     * @var array
     */
    protected static $extensions = [];

    /**
     * @var \T4\Dbal\Connection[]
     */
    private static $connections;

    /**
     * @return array
     */
    public static function getSchema()
    {
        static $schema = null;
        if (null === $schema) {
            $class = get_called_class();
            $schema = $class::$schema;
            $extensions = $class::getExtensions();
            foreach ($extensions as $extension) {
                $extensionClassName = '\\T4\\Orm\\Extensions\\' . ucfirst($extension);
                if (class_exists($extensionClassName)) {
                    $extension = new $extensionClassName;
                    $schema['columns'] = $extension->prepareColumns($schema['columns'], $class);
                    $schema['relations'] = $extension->prepareRelations(isset($schema['relations']) ? $schema['relations'] : [], $class);
                }
            }
        }
        return $schema;
    }

    /**
     * @return array
     */
    public static function getColumns()
    {
        return static::getSchema()['columns'];
    }

    /**
     * @param string $class
     * @param string $relationName
     * @return array mixed
     */
    public static function getPivots($class, $relationName)
    {
        $schema = static::getSchema();
        if (empty($schema['pivots']) || empty($schema['pivots'][$class]) || empty($schema['pivots'][$class][$relationName])) {
            return [];
        } else {
            return $schema['pivots'][$class][$relationName];
        }
    }

    /**
     * @return string
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

    /**
     * @return string
     */
    public static function getDbConnectionName()
    {
        $schema = static::getSchema();
        return !empty($schema['db']) ? $schema['db'] : 'default';
    }

    /**
     * @param string|\T4\Dbal\Connection $connection
     */
    public static function setConnection($connection)
    {
        if (is_string($connection)) {
            if ('cli' == PHP_SAPI) {
                $app = \T4\Console\Application::instance();
            } else {
                $app = \T4\Mvc\Application::instance();
            }
            $connection = $app->db->{$connection};
        }
        self::$connections[get_called_class()] = $connection;
    }

    /**
     * @return \T4\Dbal\Connection
     */
    public static function getDbConnection()
    {
        if ( !isset(self::$connections[get_called_class()]) ) {
            static::setConnection(static::getDbConnectionName());
        }
        return self::$connections[get_called_class()];
    }

    /**
     * @return \T4\Dbal\IDriver
     */
    public static function getDbDriver()
    {
        return static::getDbConnection()->getDriver();
    }

    /**
     * @return array
     */
    public static function getExtensions()
    {
        return !empty(static::$extensions) ?
            array_merge(['standard', 'relations'], static::$extensions) :
            ['standard', 'relations'];
    }

}