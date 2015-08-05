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

    use TMysqlQueryBuilder;

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
                $ddl = 'BIGINT UNSIGNED NOT NULL DEFAULT \'0\'';
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
                $ddl = 'TEXT';
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
            default:
                $ddl = 'VARCHAR(' . (isset($options['length']) ? (int)$options['length'] : 255) . ')';
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
        if (!$hasPK) {
            array_unshift($columnsDDL, $this->createColumnDDL(Model::PK, ['type' => 'pk']));
            array_unshift($indexesDDL, $this->createIndexDDL('', ['type'=>'primary', 'columns'=>[Model::PK]]));
        }

        foreach ($indexes as $name => $options) {
            $indexesDDL[] = $this->createIndexDDL(is_numeric($name) ? '' : $name, $options);
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
            ->where('`' . $column . '`=:value' . (!empty($options['where']) ? ' AND (' . $options['where'] . ')' : ''))
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
            ->where('`' . $column . '`=:value')
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
        $sets = [];
        $data = [];

        foreach ($columns as $column => $def) {
            if (isset($model->{$column}) && !is_null($model->{$column})) {
                $cols[] = $column;
                $sets[$column] = ':' . $column;
                $data[':'.$column] = $model->{$column};
            } elseif (isset($def['default'])) {
                $sets[$column] = ':' . $column;
                $data[':'.$column] = $def['default'];
            }
        }

        foreach ($relations as $rel => $def) {
            switch ($def['type']) {
                case $class::BELONGS_TO:
                    $column = $class::getRelationLinkName($def);
                    if (!in_array($column, $cols)) {
                        if (isset($model->{$column}) && !is_null($model->{$column})) {
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
            $sql = new QueryBuilder();
            $sql->insert($class::getTableName())
                ->values($sets);
            $connection->execute($sql, $data);
            $model->{$class::PK} = $connection->lastInsertId();
        } else {
            $sql = new QueryBuilder();
            $sql->update($class::getTableName())
                ->values($sets)
                ->where($class::PK . '=' . $model->getPk());
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
                        $sql = 'DELETE FROM `' . $table . '` WHERE `' . $class::getManyToManyThisLinkColumnName() . '`=:id';
                        $connection->execute($sql, [':id'=>$model->getPk()]);
                        if (!empty($sets)) {
                            $sql = 'INSERT INTO `' . $table . '`
                                    (`' . $class::getManyToManyThisLinkColumnName() . '`, `' . $class::getManyToManyThatLinkColumnName($relation) . '`)
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
            DELETE FROM `' . $class::getTableName() . '`
            WHERE `' . $class::PK . '`=\'' . $model->{$class::PK} . '\'
        ';
        $connection->execute($sql);

    }

}