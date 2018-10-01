<?php

namespace T4\Dbal\Drivers;

use T4\Core\Collection;
use T4\Dbal\Connection;
use T4\Dbal\Exception;
use T4\Dbal\IDriver;
use T4\Dbal\Query;
use T4\Dbal\QueryBuilder;
use T4\Orm\Model;

class Pgsql
    implements IDriver
{

    use TPgsqlQueryBuilder;
    use TPgsqlQuery;

    protected $selectNoQouteTemplate = '~distinct|count|avg|group_concat|min|max|sum~i';

    public function quoteName($name)
    {
        $parts = explode('.', $name);
        $lastIndex = count($parts) - 1;
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
                $part = '"' . $part . '"';
            }
        }
        return implode('.', $parts);
    }

    protected function createColumnDDL($table, $name, $options)
    {
        $name = $this->quoteName($name);
        switch ($options['type']) {
            case 'pk':
                if (isset($options['autoincrement']) && false == $options['autoincrement']) {
                    $ddl = 'BIGINT NOT NULL DEFAULT \'0\' PRIMARY KEY';
                } else {
                    $ddl = 'BIGSERIAL PRIMARY KEY';
                }
                break;
            case 'relation':
            case 'link':
                $ddl = 'BIGINT';
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
                    case 'small':
                        $ddl = 'SMALLINT';
                        break;
                    case 'long':
                    case 'big':
                        $ddl = 'BIGINT';
                        break;
                    case 'medium':
                    default:
                        $ddl = 'INTEGER';
                        break;
                }
                break;
            case 'float':
            case 'real':
                $ddl = 'REAL';
                break;
            case 'text':
                $ddl = 'TEXT';
                break;
            case 'json':
                $ddl = 'JSON';
                break;
            case 'jsonb':
                $ddl = 'JSONB';
                break;
            case 'datetime':
                $ddl = 'TIMESTAMP';
                break;
            case 'date':
                $ddl = 'DATE';
                break;
            case 'time':
                $ddl = 'TIME';
                break;
            case 'char':
                $ddl = 'CHARACTER(' . (isset($options['length']) ? (int)$options['length'] : 255) . ')';
                break;
            case 'string':
                $ddl = 'VARCHAR(' . (isset($options['length']) ? (int)$options['length'] : 255) . ')';
                break;
            default:
                $ddl = $options['type'];
                break;
        }

        if (isset($options['default'])) {
            $default = 'ALTER TABLE ' . $this->quoteName($table) . ' ALTER COLUMN ' . $name . ' SET DEFAULT \'' . $options['default'] . '\'';
            return [$name . ' ' . $ddl, $default];
        }

        return $name . ' ' . $ddl;
    }

    protected function createIndexDDL($tableName, $name = '', $options)
    {

        if (!isset($options['type']))
            $options['type'] = '';

        if ('primary' == $options['type']) {
            $constraintName = (!empty($name) ? $this->quoteName($name) . ' ' : $this->quoteName($tableName . '_pkey '));
            $ddl = 'ALTER TABLE ' . $this->quoteName($tableName) . ' ADD CONSTRAINT ' . $constraintName . 'PRIMARY KEY';
        } else {
            $indexName = (!empty($name) ? $this->quoteName($name) . ' ' : '');
            $ddl = 'CREATE ' . (('unique' == $options['type']) ? 'UNIQUE ' : '') . 'INDEX ' . $indexName . 'ON ' . $this->quoteName($tableName);
        }

        $driver = $this;
        $options['columns'] = array_map(function ($x) use ($driver) {
            return $driver->quoteName($x);
        }, $options['columns']);
        $ddl .= ' (' . implode(', ', $options['columns']) . ')';

        if (!empty($options['where'])) {
            if ('primary' != $options['type']) {
                $ddl .= ' WHERE ' . $options['where'];
            }
        }

        return $ddl;
    }

    protected function createTableDDL($tableName, $columns = [], $indexes = [], $extensions = [])
    {

        foreach ($extensions as $extension) {
            $extensionClassName = '\\T4\\Orm\\Extensions\\' . ucfirst($extension);
            $extension = new $extensionClassName;
            $columns = $extension->prepareColumns($columns);
            $indexes = $extension->prepareIndexes($indexes);
        }

        $columnsDDL = [];
        $extraDDL = [];

        $hasPK = false;
        foreach ($columns as $name => $options) {

            $columnDDLs = $this->createColumnDDL($tableName, $name, $options);
            if (is_array($columnDDLs)) {
                $columnsDDL[] = array_shift($columnDDLs);
                $extraDDL = array_merge($extraDDL, $columnDDLs);
            } else {
                $columnsDDL[] = $columnDDLs;
            }

            if ('pk' == $options['type']) {
                $hasPK = true;
            }

            if ('link' == $options['type']) {
                $indexes[] = ['type' => 'index', 'columns' => [$name]];
            }

        }

        $indexesDDL = [];
        $columnsUsed = [];

        foreach ($indexes as $name => $options) {
            if (in_array($options['columns'], $columnsUsed)) {
                break;
            }
            if (is_numeric($name)) {
                $name = '';
            }
            $indexesDDL[] = $this->createIndexDDL($tableName, $name, $options);
            $columnsUsed[] = $options['columns'];
            if (isset($options['type']) && 'primary' == $options['type']) {
                $hasPK = true;
            }
        }

        if (!$hasPK) {
            array_unshift($columnsDDL, $this->createColumnDDL($tableName, Model::PK, ['type' => 'pk']));
        }

        $createTableDDL = 'CREATE TABLE ' . $this->quoteName($tableName) . "\n" . '(' . implode(', ', array_unique($columnsDDL)) . ')';
        return array_merge([$createTableDDL], $indexesDDL, $extraDDL);
    }

    public function createTable(Connection $connection, $tableName, $columns = [], $indexes = [], $extensions = [])
    {
        foreach ($this->createTableDDL($tableName, $columns, $indexes, $extensions) as $query) {
            $connection->execute($query);
        }
    }

    public function existsTable(Connection $connection, $tableName)
    {
        $sql = 'SELECT COUNT(*) FROM pg_tables where tablename=:table';
        $result = $connection->query($sql, [':table' => $tableName]);
        return 0 != $result->fetchScalar();
    }

    public function renameTable(Connection $connection, $tableName, $tableNewName)
    {
        $sql = 'ALTER TABLE ' . $this->quoteName($tableName) . ' RENAME TO ' . $this->quoteName($tableNewName);
        $connection->execute($sql);
    }

    public function truncateTable(Connection $connection, $tableName)
    {
        $connection->execute('TRUNCATE TABLE ' . $this->quoteName($tableName) . '');
    }

    public function dropTable(Connection $connection, $tableName)
    {
        $connection->execute('DROP TABLE ' . $this->quoteName($tableName) . '');
    }

    public function addColumn(Connection $connection, $tableName, array $columns)
    {
        $sql = 'ALTER TABLE ' . $this->quoteName($tableName) . '';
        $columnsDDL = [];
        $extraDDL = [];

        foreach ($columns as $name => $options) {
            $columnDDLs = $this->createColumnDDL($tableName, $name, $options);
            if (is_array($columnDDLs)) {
                $columnsDDL[] = 'ADD COLUMN ' . array_shift($columnDDLs);
                $extraDDL = array_merge($extraDDL, $columnDDLs);
            } else {
                $columnsDDL[] = 'ADD COLUMN ' . $columnDDLs;
            }
        }
        $sql .= ' ' .
            implode(', ', $columnsDDL) .
            '';
        $connection->execute($sql);
        foreach ($extraDDL as $ddl) {
            $connection->execute($ddl);
        }
    }

    public function dropColumn(Connection $connection, $tableName, array $columns)
    {
        $sql = 'ALTER TABLE ' . $this->quoteName($tableName) . '';
        $columnsDDL = [];
        foreach ($columns as $name) {
            $columnsDDL[] = 'DROP COLUMN ' . $this->quoteName($name) . '';
        }
        $sql .= ' ' .
            implode(', ', $columnsDDL) .
            '';
        $connection->execute($sql);
    }

    public function renameColumn(Connection $connection, $tableName, $oldName, $newName)
    {
        $sql = 'SHOW CREATE TABLE `' . $tableName . '`';
        $result = $connection->query($sql)->fetch()['Create Table'];
        preg_match('~^[\s]+\`' . $oldName . '\`[\s]+(.*?)[\,]?$~m', $result, $m);
        $sql = '
            ALTER TABLE `' . $tableName . '`
            CHANGE `' . $oldName . '` `' . $newName . '` ' . $m[1];
        $connection->execute($sql);
    }

    public function addIndex(Connection $connection, $tableName, array $indexes)
    {
        foreach ($indexes as $name => $options) {
            $ddl = 'CREATE ' . $this->createIndexDDL($tableName, is_numeric($name) ? '' : $name, $options);
            $connection->execute($ddl);
        }
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

    public function insert(Connection $connection, $tableName, array $data)
    {
        $sql = 'INSERT INTO ' . $this->quoteName($tableName) . '';
        $sql .= ' ("' . implode('", "', array_keys($data)) . '")';
        $sql .= ' VALUES';
        $values = [];
        foreach ($data as $key => $val)
            $values[':' . $key] = $val;
        $sql .= ' (' . implode(', ', array_keys($values)) . ')';
        $connection->execute($sql, $values);
        $sequenceName = $this->getSequenceName($connection, $tableName);
        return empty($sequenceName) ? null : $connection->lastInsertId($sequenceName);
    }

    public function findAllByQuery($class, $query, $params = [])
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
        if ('pgsql' != $class::getDbConnection()->getDriverName()) {
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
        if ('pgsql' != $class::getDbConnection()->getDriverName()) {
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
        if ('pgsql' != $class::getDbConnection()->getDriverName()) {
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
        if ('pgsql' != $class::getDbConnection()->getDriverName()) {
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
        /** @var \T4\Orm\Model $class */
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
        if ('pgsql' != $class::getDbConnection()->getDriverName()) {
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
        if ('pgsql' != $class::getDbConnection()->getDriverName()) {
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
     * get name of the sequence that a serial or bigserial column uses
     * @param Connection $connection
     * @param string $tableName
     *
     * @return string
     */
    protected function getSequenceName(Connection $connection, $tableName)
    {
        $PkColumns = $connection->query("SELECT a.attname, format_type(a.atttypid, a.atttypmod) AS data_type
            FROM   pg_index i
            JOIN   pg_attribute a ON a.attrelid = i.indrelid
                                 AND a.attnum = ANY(i.indkey)
            WHERE  i.indrelid = :table_name::regclass
            AND    i.indisprimary;", [':table_name' => $tableName])->fetchAll(\PDO::FETCH_ASSOC);
        $PkColumnsCount = count($PkColumns);
        if ($PkColumnsCount != 1) {
            return null;
        }

        return $connection->query('select pg_get_serial_sequence(:table_name, :column_name)', [':table_name' => $tableName, ':column_name' => $PkColumns[0]['attname']])->fetchScalar();
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
        $prep = [];
        $sets = [];
        $data = [];

        foreach ($columns as $column => $def) {
            if (isset($model->{$column}) && null === $model->{$column} && isset($def['default'])) {
                $cols[] = $this->quoteName($column);
                $prep[] = ':' . $column;
                $sets[] = '' . $this->quoteName($column) . '=:' . $column;
                $data[':' . $column] = $def['default'];
            } else {
                $cols[] = $this->quoteName($column);
                $prep[] = ':' . $column;
                $sets[] = '' . $this->quoteName($column) . '=:' . $column;
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
                        // @todo: test this!
                        // if (isset($model->{$column}) && !is_null($model->{$column})) {
                        if (isset($model->{$column})) {
                            $cols[] = $this->quoteName($column);
                            $prep[] = ':' . $column;
                            $sets[] = '' . $this->quoteName($column) . '=:' . $column;
                            $data[':' . $column] = $model->{$column};
                        } elseif (isset($model->{$rel}) && $model->{$rel} instanceof Model) {
                            $cols[] = $this->quoteName($column);
                            $prep[] = ':' . $column;
                            $sets[] = '' . $this->quoteName($column) . '=:' . $column;
                            $data[':' . $column] = $model->{$rel}->getPk();
                        }
                    }
                    break;
            }
        }

        $connection = $class::getDbConnection();
        if ($model->isNew()) {
            $sql = '
                INSERT INTO ' . $this->quoteName($class::getTableName()) . '
                (' . implode(', ', array_unique($cols)) . ')
                VALUES
                (' . implode(', ', array_unique($prep)) . ')
                RETURNING ' . $class::PK . '
            ';
            $res = $connection->query($sql, $data);
            $model->{$class::PK} = $res->fetch()[$class::PK];
        } else {
            $sql = '
                UPDATE ' . $this->quoteName($class::getTableName()) . '
                SET ' . implode(', ', array_unique($sets)) . '
                WHERE "' . $class::PK . '"=\'' . $model->{$class::PK} . '\'
            ';
            $connection->execute($sql, $data);
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