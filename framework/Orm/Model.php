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
    static protected $schema = [];

    /**
     * @var array
     */
    static protected $extensions = [];

    /**
     * @var \T4\Dbal\Connection
     */
    static protected $connection;

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
    static public function getColumns()
    {
        return static::getSchema()['columns'];
    }

    /**
     * @param string $class
     * @param string $relationName
     * @return array mixed
     */
    static public function getPivots($class, $relationName)
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
     * @param string|\T4\Dbal\Connection $connection
     */
    static public function setConnection($connection)
    {
        if (is_string($connection)) {
            if ('cli' == PHP_SAPI) {
                $app = \T4\Console\Application::instance();
            } else {
                $app = \T4\Mvc\Application::instance();
            }
            static::$connection = $app->db->{$connection};
        } elseif ($connection instanceof Connection) {
            static::$connection = $connection;
        }
    }

    /**
     * @return \T4\Dbal\Connection
     */
    static public function getDbConnection()
    {
        if (null == static::$connection) {
            static::setConnection(static::getDbConnectionName());
        }
        return static::$connection;
    }

    /**
     * @return \T4\Dbal\IDriver
     */
    static public function getDbDriver()
    {
        return static::getDbConnection()->getDriver();
    }

    /**
     * @return array
     */
    static public function getExtensions()
    {
        return !empty(static::$extensions) ?
            array_merge(['standard', 'relations'], static::$extensions) :
            ['standard', 'relations'];
    }

}