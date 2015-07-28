<?php

namespace T4\Dbal\Drivers;

use T4\Core\Collection;
use T4\Dbal\Connection;
use T4\Dbal\IDriver;
use T4\Dbal\QueryBuilder;
use T4\Orm\Model;

class Pgsql
    implements IDriver
{

    use TPgsqlQueryBuilder;

    protected $selectNoQouteTemplate = '~count|avg|group_concat|min|max|sum~i';

    public function quoteName($name)
    {
        $parts = explode('.', $name);
        $lastIndex = count($parts)-1;
        foreach ($parts as $index => &$part) {
            if ('*' == $part) {
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
                $ddl = 'BIGINT NOT NULL DEFAULT \'0\'';
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
            case 'json':
                $ddl = 'TEXT';
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
            default:
                if (isset($options['length'])) {
                    $ddl = 'VARCHAR(' . (int)$options['length'] . ')';
                } else {
                    $ddl = 'VARCHAR';
                }
                break;
        }

        if(isset($options['default'])) {
            $default = 'ALTER TABLE ' . $this->quoteName($table) . ' ALTER COLUMN ' . $name . ' SET DEFAULT \'' . $options['default'] .'\'';
            return [$name . ' ' . $ddl, $default];
        }

        return $name . ' ' . $ddl;
    }

    protected function createIndexDDL($tableName, $name='', $options='')
    {

        if (!isset($options['type']))
            $options['type'] = '';

        $ddl  = 'INDEX ' . (!empty($name) ? $this->quoteName($name) . ' ' : '') . 'ON ' . $this->quoteName($tableName);
        if ('unique' == $options['type']) {
            $ddl = 'UNIQUE ' . $ddl;
        }

        $driver = $this;
        $options['columns'] = array_map(function ($x) use ($driver) {
            return $driver->quoteName($x);
        }, $options['columns']);
        $ddl .= ' (' . implode(', ', $options['columns']) . ')';

        if (!empty($options['where'])) {
            $ddl .= ' WHERE ' . $options['where'];
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
                $indexes[] = ['type'=>'index', 'columns'=>[$name]];
            }

        }

        if (!$hasPK) {
            array_unshift($columnsDDL, $this->createColumnDDL($tableName, Model::PK, ['type' => 'pk']));
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
            $indexesDDL[] = 'CREATE '. $this->createIndexDDL($tableName, $name, $options);
            $columnsUsed[] = $options['columns'];
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
        preg_match('~^[\s]+\`'.$oldName.'\`[\s]+(.*?)[\,]?$~m', $result, $m);
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
        $sql  = 'INSERT INTO ' . $this->quoteName($tableName) . '';
        $sql .= ' ("' . implode('", "', array_keys($data)) . '")';
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
        $result = $class::getDbConnection()->query($query, $params)->fetchAll(\PDO::FETCH_CLASS, $class);
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
        $result = $class::getDbConnection()->query($query, $params)->fetchObject($class);
        if (!empty($result))
            $result->setNew(false);
        return $result;
    }

    public function findAll($class, $options = [])
    {
        $query = new QueryBuilder();
        $query
            ->select('*')
            ->from($class::getTableName())
            ->where(!empty($options['where']) ? $options['where'] : '')
            ->order(!empty($options['order']) ? $options['order'] : '')
            ->offset(!empty($options['offset']) ? $options['offset'] : '')
            ->limit(!empty($options['limit']) ? $options['limit'] : '')
            ->params(!empty($options['params']) ? $options['params'] : []);
        return $this->findAllByQuery($class, $query);
    }

    public function find($class, $options = [])
    {
        $query = new QueryBuilder();
        $query
            ->select('*')
            ->from($class::getTableName())
            ->where(!empty($options['where']) ? $options['where'] : '')
            ->order(!empty($options['order']) ? $options['order'] : '')
            ->offset(!empty($options['offset']) ? $options['offset'] : '')
            ->limit(1)
            ->params(!empty($options['params']) ? $options['params'] : []);
        return $this->findByQuery($class, $query);
    }

    public function findAllByColumn($class, $column, $value, $options = [])
    {
        $query = new QueryBuilder();
        $query
            ->select('*')
            ->from($class::getTableName())
            ->where('' . $this->quoteName($column) . '=:value' . (!empty($options['where']) ? ' AND (' . $options['where'] . ')' : ''))
            ->order(!empty($options['order']) ? $options['order'] : '')
            ->offset(!empty($options['offset']) ? $options['offset'] : '')
            ->limit(!empty($options['limit']) ? $options['limit'] : '')
            ->params([':value' => $value] + (!empty($options['params']) ? $options['params'] : []));
        return $this->findAllByQuery($class, $query);
    }

    public function findByColumn($class, $column, $value, $options = [])
    {
        $query = new QueryBuilder();
        $query
            ->select('*')
            ->from($class::getTableName())
            ->where('' . $this->quoteName($column) . '=:value')
            ->order(!empty($options['order']) ? $options['order'] : '')
            ->offset(!empty($options['offset']) ? $options['offset'] : '')
            ->limit(1)
            ->params([':value' => $value]);
        return $this->findByQuery($class, $query);
    }

    public function countAll($class, $options = [])
    {
        $query = new QueryBuilder();
        $query
            ->select('COUNT(*)')
            ->from($class::getTableName())
            ->where(!empty($options['where']) ? $options['where'] : '')
            ->params(!empty($options['params']) ? $options['params'] : []);

        return $class::getDbConnection()->query($query->getQuery($this), $query->getParams())->fetchScalar();
    }

    public function countAllByColumn($class, $column, $value, $options = [])
    {
        $query = new QueryBuilder();
        $query
            ->select('COUNT(*)')
            ->from($class::getTableName())
            ->where('`' . $column . '`=:value')
            ->params([':value' => $value]);

        return $class::getDbConnection()->query($query->getQuery($this), $query->getParams())->fetchScalar();
    }

    /**
     * TODO: много лишних isset, которые всегда true по определению
     * Сохранение полей модели без учета связей, требующих ID модели
     * @param Model $model
     * @return Model
     */
    protected function saveColumns(Model $model)
    {
        $class = get_class($model);
        $columns = $class::getColumns();
        $relations = $class::getRelations();
        $cols = [];
        $prep = [];
        $sets = [];
        $data = [];

        foreach ($columns as $column => $def) {
            if (isset($model->{$column}) && !is_null($model->{$column})) {
                $cols[] = $this->quoteName($column);
                $prep[] = ':' . $column;
                $sets[] = '' . $this->quoteName($column) . '=:' . $column;
                $data[':'.$column] = $model->{$column};
            } elseif (isset($def['default'])) {
                $cols[] = $this->quoteName($column);
                $prep[] = ':' . $column;
                $sets[] = '' . $this->quoteName($column) . '=:' . $column;
                $data[':'.$column] = $def['default'];
            }
        }

        foreach ($relations as $rel => $def) {
            switch ($def['type']) {
                case $class::BELONGS_TO:
                    $column = $class::getRelationLinkName($def);
                    if (!in_array($column, $cols)) {
                        if (isset($model->{$column}) && !is_null($model->{$column})) {
                            $cols[] = $this->quoteName($column);
                            $prep[] = ':' . $column;
                            $sets[] = '' . $this->quoteName($column) . '=:' . $column;
                            $data[':'.$column] = $model->{$column};
                        } elseif (isset($model->{$rel}) && $model->{$rel} instanceof Model) {
                            $cols[] = $this->quoteName($column);
                            $prep[] = ':' . $column;
                            $sets[] = '' . $this->quoteName($column) . '=:' . $column;
                            $data[':'.$column] = $model->{$rel}->getPk();
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
        $class = get_class($model);
        $relations = $class::getRelations();
        $connection = $class::getDbConnection();

        /*
         * TODO это тут лишнее, перенести в saveColumns
         * Сохраняем связанные данные, которым не требуется ID нашей записи
         */
        foreach ($relations as $key => $relation) {
            switch ($relation['type']) {
                case $class::BELONGS_TO:
                    $column = $class::getRelationLinkName($relation);
                    if (!empty($model->{$key}) && $model->{$key} instanceof Model ) {
                        if ( $model->{$key}->isNew() ) {
                            $model->{$key}->save();
                        }
                        $model->{$column} = $model->{$key}->getPk();
                    }
                    break;
            }
        }

        /*
         * Сохраняем поля самой модели
         */
        $this->saveColumns($model);

        /*
        * И еще раз сохраняем связанные данные, которым требовался ID нашей записи
        */
        foreach ($relations as $key => $relation) {
            switch ($relation['type']) {

                case $class::HAS_ONE:
                    if (!empty($model->{$key}) && $model->{$key} instanceof Model ) {
                        $column = $class::getRelationLinkName($relation);
                        $subModel = $model->{$key};
                        $subModel->{$column} = $model->getPk();
                        $subModel->save();
                    }
                    break;

                case $class::HAS_MANY:
                    if (!empty($model->{$key}) && $model->{$key} instanceof Collection ) {
                        $column = $class::getRelationLinkName($relation);
                        foreach ( $model->{$key} as $subModel) {
                            $subModel->{$column} = $model->getPk();
                            $subModel->save();
                        }
                    }
                    break;

                case $class::MANY_TO_MANY:
                    if (!empty($model->{$key}) && $model->{$key} instanceof Collection ) {
                        $sets = [];
                        foreach ( $model->{$key} as $subModel ) {
                            if ($subModel->isNew()) {
                                $this->saveColumns($subModel);
                            }
                            $sets[] = '(' . $model->getPk() . ',' . $subModel->getPk() . ')';
                        }

                        $table = $class::getRelationLinkName($relation);
                        $sql = 'DELETE FROM ' . $this->quoteName($table) . ' WHERE "' . $class::getManyToManyThisLinkColumnName() . '"=:id';
                        $connection->execute($sql, [':id'=>$model->getPk()]);
                        if (!empty($sets)) {
                            $sql = 'INSERT INTO ' . $this->quoteName($table) . '
                                    ("' . $class::getManyToManyThisLinkColumnName() . '", "' . $class::getManyToManyThatLinkColumnName($relation) . '")
                                    VALUES
                                    ' . (implode(', ', $sets)) . '
                                    ';
                            $connection->execute($sql);
                        }
                    }
                    break;

            }
        }

    }

    public function delete(Model $model)
    {

        $class = get_class($model);
        $connection = $class::getDbConnection();

        $sql = '
            DELETE FROM ' . $this->quoteName($class::getTableName()) . '
            WHERE ' . $class::PK . '=:id
        ';
        $connection->execute($sql, [':id' => $model->getPk()]);

    }

}