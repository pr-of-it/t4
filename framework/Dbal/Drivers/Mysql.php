<?php

namespace T4\Dbal\Drivers;

use T4\Core\Collection;
use T4\Dbal\Connection;
use T4\Dbal\Exception;
use T4\Dbal\IDriver;
use T4\Dbal\Query;
use T4\Dbal\QueryBuilder;
use T4\Orm\Model;

class Mysql
    implements IDriver
{

    use TMysqlQueryBuilder;
    use TMysqlQuery;

    protected $selectNoQouteTemplate = '~count|avg|group_concat|min|max|sum~i';

    public function quoteName($name)
    {
        $parts = explode('.', $name);
        $lastIndex = count($parts)-1;
        foreach ($parts as $index => &$part) {
            if ('*' == $part) {
                continue;
            }
            if (false !== strpos($part, ')') || false !== strpos($part, '(')) {
                continue;
            }
            if (
                (
                    $index == $lastIndex
                    ||
                    !preg_match('~^(t|j)[\d]+$~', $part)
                ) &&
                !preg_match($this->selectNoQouteTemplate, $part)
            ) {
                $part = '`' . $part . '`';
            }
        }
        return implode('.', $parts);
    }

    protected function createColumnDDL($name, $options)
    {
        $name = $this->quoteName($name);

        switch ($options['type']) {
            case 'pk':
                if (isset($options['autoincrement']) && false == $options['autoincrement']) {
                    $ddl = 'BIGINT UNSIGNED NOT NULL';
                } else {
                    $ddl = 'SERIAL';
                }
                break;
            case 'relation':
            case 'link':
                $ddl = 'BIGINT UNSIGNED NULL DEFAULT NULL';
                break;
            case 'serial':
                $ddl = 'SERIAL';
                break;
            case 'boolean':
            case 'bool':
                $ddl = 'BOOLEAN';
                break;
            case 'int':
            case 'integer':
                $options['length'] = isset($options['length']) ? $options['length'] : '';
                switch (strtolower($options['length'])) {
                    case 'tiny':
                        $ddl = 'TINYINT';
                        break;
                    case 'small':
                        $ddl = 'SMALLINT';
                        break;
                    case 'medium':
                        $ddl = 'MEDIUMINT';
                        break;
                    case 'long':
                    case 'big':
                        $ddl = 'BIGINT';
                        break;
                    default:
                        $ddl = 'INT';
                        break;
                }
                $ddl .= isset($options['dimension']) ? '(' . $options['dimension'] . ')' : '';
                break;
            case 'float':
            case 'real':
                isset($options['dimension']) ? $ddl = 'FLOAT(' . $options['dimension'] . ')' : $ddl = 'FLOAT';
                break;
            case 'text':
                $options['length'] = isset($options['length']) ? $options['length'] : '';
                switch (strtolower($options['length'])) {
                    case 'tiny':
                    case 'small':
                        $ddl = 'TINYTEXT';
                        break;
                    case 'medium':
                        $ddl = 'MEDIUMTEXT';
                        break;
                    case 'long':
                    case 'big':
                        $ddl = 'LONGTEXT';
                        break;
                    default:
                        $ddl = 'TEXT';
                        break;
                }
                break;
            case 'json':
            case 'jsonb':
                $ddl = 'LONGTEXT';
                break;
            case 'datetime':
                $ddl = 'DATETIME';
                break;
            case 'date':
                $ddl = 'DATE';
                break;
            case 'time':
                $ddl = 'TIME';
                break;
            case 'char':
                $ddl = 'CHAR(' . (isset($options['length']) ? (int)$options['length'] : 255) . ')';
                break;
            case 'string':
                $ddl = 'VARCHAR(' . (isset($options['length']) ? (int)$options['length'] : 255) . ')';
                break;
            default:
                $ddl = $options['type'];
                break;
        }

        if(isset($options['default'])) {
            $ddl .= ' ' . 'NOT NULL DEFAULT' . ' \'' . $options['default'] .'\'';
        }

        return $name . ' ' . $ddl;
    }

    protected function createIndexDDL($name='', $options)
    {
        if (empty($name)) {
            $name = implode('_', $options['columns']) . '_idx';
        }

        if (!isset($options['type']))
            $options['type'] = '';

        $driver = $this;
        $columns = array_map(function ($x) use ($driver) {
            return $driver->quoteName($x);
        }, $options['columns']);

        if ($options['type'] == 'primary') {
            $ddl = '(' . implode(', ', $columns) . ')';
        } else {
            $ddl = $this->quoteName($name) . ' (' . implode(', ', $columns) . ')';
        }

        switch ($options['type']) {
            case 'primary':
                return 'PRIMARY KEY ' . $ddl;
            case 'unique':
                return 'UNIQUE INDEX ' . $ddl;
            default:
                return 'INDEX ' . $ddl;
        }

    }

    protected function createTableDDL($tableName, $columns = [], $indexes = [], $extensions = [])
    {
        foreach ($extensions as $extension) {
            $extensionClassName = '\\T4\\Orm\\Extensions\\' . ucfirst($extension);
            $extension = new $extensionClassName;
            $columns = $extension->prepareColumns($columns);
            $indexes = $extension->prepareIndexes($indexes);
        }

        $sql = 'CREATE TABLE ' . $this->quoteName($tableName) . "\n";

        $columnsDDL = [];
        $indexesDDL = [];

        $hasPK = false;

        foreach ($columns as $name => $options) {
            $columnsDDL[] = $this->createColumnDDL($name, $options);
            if ('pk' == $options['type']) {
                $indexesDDL[] = $this->createIndexDDL('', ['type'=>'primary', 'columns'=>[$name]]);
                $hasPK = true;
            }
            if ('link' == $options['type']) {
                $indexesDDL[] = $this->createIndexDDL('', ['type'=>'index', 'columns'=>[$name]]);
            }
        }

        foreach ($indexes as $name => $options) {
            $indexesDDL[] = $this->createIndexDDL(is_numeric($name) ? '' : $name, $options);
            if ('primary' == $options['type']) {
                $hasPK = true;
            }
        }

        if (!$hasPK) {
            array_unshift($columnsDDL, $this->createColumnDDL(Model::PK, ['type' => 'pk']));
            array_unshift($indexesDDL, $this->createIndexDDL('', ['type'=>'primary', 'columns'=>[Model::PK]]));
        }

        $sql .= "(\n" .
            implode(",\n", array_unique($columnsDDL)) . ",\n" .
            implode(",\n", array_unique($indexesDDL)) .
            "\n)";
        return $sql;
    }

    public function createTable(Connection $connection, $tableName, $columns = [], $indexes = [], $extensions = [])
    {
        $connection->execute($this->createTableDDL($tableName, $columns, $indexes, $extensions));
    }

    public function existsTable(Connection $connection, $tableName)
    {
        $sql = 'SHOW TABLES LIKE \'' . $tableName . '\'';
        $result = $connection->query($sql);
        return 0 != count($result->fetchAll());
    }

    public function renameTable(Connection $connection, $tableName, $tableNewName)
    {
        $sql = 'RENAME TABLE ' . $this->quoteName($tableName) . ' TO ' . $this->quoteName($tableNewName);
        $connection->execute($sql);
    }

    public function truncateTable(Connection $connection, $tableName)
    {
        $connection->execute('TRUNCATE TABLE ' . $this->quoteName($tableName));
    }

    public function dropTable(Connection $connection, $tableName)
    {
        $connection->execute('DROP TABLE ' . $this->quoteName($tableName));
    }

    public function addColumn(Connection $connection, $tableName, array $columns)
    {
        $sql = 'ALTER TABLE ' . $this->quoteName($tableName);
        $columnsDDL = [];
        foreach ($columns as $name => $options) {
            $columnsDDL[] = 'ADD COLUMN ' . $this->createColumnDDL($name, $options);
        }
        $sql .= ' ' .
            implode(', ', $columnsDDL) .
            '';
        $connection->execute($sql);
    }

    public function dropColumn(Connection $connection, $tableName, array $columns)
    {
        $sql = 'ALTER TABLE ' . $this->quoteName($tableName);
        $columnsDDL = [];
        foreach ($columns as $name) {
            $columnsDDL[] = 'DROP COLUMN ' . $this->quoteName($name);
        }
        $sql .= ' ' .
            implode(', ', $columnsDDL) .
            '';
        $connection->execute($sql);
    }

    public function renameColumn(Connection $connection, $tableName, $oldName, $newName)
    {
        $sql = 'SHOW CREATE TABLE ' . $this->quoteName($tableName);
        $result = $connection->query($sql)->fetch()['Create Table'];
        preg_match('~^[\s]+\`'.$oldName.'\`[\s]+(.*?)[\,]?$~m', $result, $m);
        $sql = '
            ALTER TABLE ' . $this->quoteName($tableName) . '
            CHANGE ' . $this->quoteName($oldName) . ' ' . $this->quoteName($newName) . ' ' . $m[1];
        $connection->execute($sql);
    }

    public function addIndex(Connection $connection, $tableName, array $indexes)
    {
        $sql = 'ALTER TABLE ' . $this->quoteName($tableName);
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
        $sql = 'ALTER TABLE ' . $this->quoteName($tableName);
        $indexesDDL = [];
        foreach ($indexes as $name) {
            $indexesDDL[] = 'DROP INDEX ' . $this->quoteName($name) . '';
        }
        $sql .= ' ' .
            implode(', ', $indexesDDL) .
            '';
        $connection->execute($sql);
    }

    public function insert(Connection $connection, $tableName, array $data)
    {
        $sql  = 'INSERT INTO ' . $this->quoteName($tableName);
        $sql .= ' (`' . implode('`, `', array_keys($data)) . '`)';
        $sql .= ' VALUES';
        $values = [];
        foreach ($data as $key => $val)
            $values[':'.$key] = $val;
        $sql .= ' (' . implode(', ', array_keys($values)) . ')';
        $connection->execute($sql, $values);
        return $connection->lastInsertId();
    }

    public function findAllByQuery($class, $query, $params=[])
    {
        if ($query instanceof QueryBuilder) {
            $params = $query->getParams();
            $query = $query->getQuery($this);
        }
        if ($query instanceof Query) {
            $params = array_merge($params, $query->params);
            $query = $this->makeQueryString($query);
        }
        /** @var \T4\Orm\Model $class */
        $result = $class::getDbConnection()->query($query, $params)->fetchAllObjects($class);
        if (!empty($result)) {
            $ret = new Collection($result);
            $ret->setNew(false);
        } else {
            $ret = new Collection();
        }
        return $ret;
    }

    public function findByQuery($class, $query, $params = [])
    {
        if ($query instanceof QueryBuilder) {
            $params = $query->getParams();
            $query = $query->getQuery($this);
        }
        if ($query instanceof Query) {
            $params = array_merge($params, $query->params);
            $query = $this->makeQueryString($query);
        }
        /** @var \T4\Orm\Model $class */
        $result = $class::getDbConnection()->query($query, $params)->fetchObject($class);
        if (!empty($result))
            $result->setNew(false);
        return $result;
    }

    /**
     * @param string $class
     * @param array|\T4\Dbal\Query $options
     * @return \T4\Core\Collection
     * @throws \T4\Dbal\Exception
     */
    public function findAll($class, $options = [])
    {
        /** @var \T4\Orm\Model $class */
        if ('mysql' != $class::getDbConnection()->getDriverName()) {
            throw new Exception('DB drivers mismatch');
        }
        $query = new Query($options);
        $query
            ->select('t1.*')
            ->from($class::getTableName());
        return $this->findAllByQuery($class, $query);
    }

    /**
     * @param string $class
     * @param array|\T4\Dbal\Query $options
     * @return mixed
     * @throws \T4\Dbal\Exception
     */
    public function find($class, $options = [])
    {
        /** @var \T4\Orm\Model $class */
        if ('mysql' != $class::getDbConnection()->getDriverName()) {
            throw new Exception('DB drivers mismatch');
        }
        $query = new Query($options);
        $query
            ->select('t1.*')
            ->from($class::getTableName())
            ->limit(1);
        return $this->findByQuery($class, $query);
    }

    /**
     * @param string $class
     * @param string $column
     * @param mixed $value
     * @param array|\T4\Dbal\Query $options
     * @return \T4\Core\Collection
     * @throws \T4\Dbal\Exception
     */
    public function findAllByColumn($class, $column, $value, $options = [])
    {
        /** @var \T4\Orm\Model $class */
        if ('mysql' != $class::getDbConnection()->getDriverName()) {
            throw new Exception('DB drivers mismatch');
        }
        $query = new Query($options);
        $query
            ->select('t1.*')
            ->from($class::getTableName())
            ->where($this->quoteName($column) . '=:columnvalue' . (!empty($query->where) ? ' AND (' . $query->where . ')' : ''))
            ->param(':columnvalue', $value);
        return $this->findAllByQuery($class, $query);
    }

    /**
     * @param string $class
     * @param string $column
     * @param mixed $value
     * @param array|\T4\Dbal\Query $options
     * @return mixed
     * @throws \T4\Dbal\Exception
     */
    public function findByColumn($class, $column, $value, $options = [])
    {
        /** @var \T4\Orm\Model $class */
        if ('mysql' != $class::getDbConnection()->getDriverName()) {
            throw new Exception('DB drivers mismatch');
        }
        $query = new Query($options);
        $query
            ->select('t1.*')
            ->from($class::getTableName())
            ->where($this->quoteName($column) . '=:columnvalue' . (!empty($query->where) ? ' AND (' . $query->where . ')' : ''))
            ->limit(1)
            ->param(':columnvalue', $value);
        return $this->findByQuery($class, $query);
    }

    public function countAllByQuery($class, $query, $params = [])
    {
        if ($query instanceof QueryBuilder) {
            $params = $query->getParams();
            $query = clone $query;
            $query = $query->select('COUNT(*)')->getQuery($this);
        }
        if ($query instanceof Query) {
            $params = array_merge($params, $query->params);
            $query = clone $query;
            $query->select('COUNT(*)');
            $query = $this->makeQueryString($query);
        }
        /** @var \T4\Orm\Model $class */
        return $class::getDbConnection()->query($query, $params)->fetchScalar();
    }

    /**
     * @param string $class
     * @param array|\T4\Dbal\Query $options
     * @return int
     * @throws \T4\Dbal\Exception
     */
    public function countAll($class, $options = [])
    {
        /** @var \T4\Orm\Model $class */
        if ('mysql' != $class::getDbConnection()->getDriverName()) {
            throw new Exception('DB drivers mismatch');
        }
        $query = new Query($options);
        $query
            ->select('COUNT(*)')
            ->from($class::getTableName());
        return (int)$class::getDbConnection()->query($query)->fetchScalar();
    }

    /**
     * @param string $class
     * @param string $column
     * @param mixed $value
     * @param array|\T4\Dbal\Query $options
     * @return int
     * @throws \T4\Dbal\Exception
     */
    public function countAllByColumn($class, $column, $value, $options = [])
    {
        /** @var \T4\Orm\Model $class */
        if ('mysql' != $class::getDbConnection()->getDriverName()) {
            throw new Exception('DB drivers mismatch');
        }
        $query = new Query($options);
        $query
            ->select('COUNT(*)')
            ->from($class::getTableName())
            ->where($this->quoteName($column) . '=:columnvalue' . (!empty($query->where) ? ' AND (' . $query->where . ')' : ''))
            ->param(':columnvalue', $value);
        return (int)$class::getDbConnection()->query($query)->fetchScalar();
    }

    /**
     * TODO: много лишних isset, которые всегда true по определению
     * Сохранение полей модели без учета связей, требующих ID модели
     * @param Model $model
     * @return Model
     */
    protected function saveColumns(Model $model)
    {
        /** @var \T4\Orm\Model $class */
        $class = get_class($model);
        $columns = $class::getColumns();
        $relations = $class::getRelations();
        $cols = [];
        $sets = [];
        $data = [];

        foreach ($columns as $column => $def) {
            if (isset($model->{$column}) && null === $model->{$column} && isset($def['default'])) {
                $sets[$column] = ':' . $column;
                $data[':'.$column] = $def['default'];
            } else {
                $cols[] = $column;
                $sets[$column] = ':' . $column;
                switch ($def['type']) {
                    case 'boolean':
                        $data[':' . $column] = (int)$model->{$column};
                        break;
                    default:
                        $data[':' . $column] = $model->{$column};
                        break;
                }
            }
        }

        foreach ($relations as $rel => $def) {
            switch ($def['type']) {
                case $class::BELONGS_TO:
                    $column = $class::getRelationLinkName($def);
                    if (!in_array($column, $cols)) {
                        // todo: test this!
                        //if (isset($model->{$column}) && !is_null($model->{$column})) {
                        if (isset($model->{$column})) {
                            $sets[$column] = ':' . $column;
                            $data[':'.$column] = $model->{$column};
                        } elseif (isset($model->{$rel}) && $model->{$rel} instanceof Model) {
                            $sets[$column] = ':' . $column;
                            $data[':'.$column] = $model->{$rel}->getPk();
                        }
                    }
                    break;
            }
        }

        $connection = $class::getDbConnection();
        if ($model->isNew()) {
            $query = new Query();
            $query->insert($class::getTableName())
                ->values($sets)
                ->params($data);
            $connection->execute($query);
            $model->{$class::PK} = $connection->lastInsertId();
        } else {
            $query = new Query();
            $query->update($class::getTableName())
                ->values($sets)
                ->where($this->quoteName($class::PK) . '='. $model->getPk())
                ->params($data);
            $connection->execute($query);
        }

        return $model;

    }

    public function save(Model $model)
    {
        $this->saveColumns($model);
    }

    public function delete(Model $model)
    {
        /** @var \T4\Orm\Model $class */
        $class = get_class($model);
        $connection = $class::getDbConnection();
        $query = (new Query())
            ->delete()
            ->from($class::getTableName())
            ->where($this->quoteName($class::PK) . '=:id')
            ->param(':id', $model->getPk());
        $connection->execute($query);
    }

}