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

    protected function createColumnDDL($options)
    {
        switch ($options['type']) {
            case 'pk':
                return 'BIGINT UNSIGNED NOT NULL AUTO_INCREMENT';
                break;
            case 'relation':
            case 'link':
                return 'BIGINT UNSIGNED NOT NULL DEFAULT \'0\'';
                break;
            case 'int':
                return 'INT(11) NOT NULL';
                break;
            case 'float':
                return 'FLOAT NOT NULL';
                break;
            case 'text':
                $options['length'] = isset($options['length']) ? $options['length'] : '';
                switch (strtolower($options['length'])) {
                    case 'tiny':
                    case 'small':
                        return 'TINYTEXT';
                        break;
                    case 'medium':
                        return 'MEDIUMTEXT';
                        break;
                    case 'long':
                    case 'big':
                        return 'LONGTEXT';
                        break;
                    default:
                        return 'TEXT';
                        break;
                }
                break;
            case 'datetime':
                return 'DATETIME';
                break;
            case 'date':
                return 'DATE';
                break;
            case 'time':
                return 'TIME';
                break;
            case 'string':
            default:
                return 'VARCHAR(' . (isset($options['length']) ? (int)$options['length'] : 255) . ') NOT NULL';
                break;
        }
    }

    protected function createIndexDDL($name, $options)
    {
        if (is_numeric($name))
            $name = implode('_', $options['columns']);
        if (!isset($options['type']))
            $options['type'] = '';
        $ddl = '`' . $name . '` (`' . implode('`,`', $options['columns']) . '`)';
        switch ($options['type']) {
            case 'unique':
                return 'UNIQUE INDEX ' . $ddl;
                break;
            default:
                return 'INDEX ' . $ddl;
                break;
        }
    }

    public function createTable(Connection $connection, $tableName, $columns = [], $indexes = [], $extensions = [])
    {

        foreach ($extensions as $extension) {
            $extensionClassName = '\\T4\\Orm\\Extensions\\' . ucfirst($extension);
            $extension = new $extensionClassName;
            $columns = $extension->prepareColumns($columns);
            $indexes = $extension->prepareIndexes($indexes);
        }

        $sql = 'CREATE TABLE `' . $tableName . '`';

        $columnsDDL = [];
        $indexesDDL = [];

        $hasPK = false;
        foreach ($columns as $name => $options) {
            $columnsDDL[] = '`' . $name . '` ' . $this->createColumnDDL($options);
            if ('pk' == $options['type']) {
                $indexesDDL[] = 'PRIMARY KEY (`' . $name . '`)';
                $hasPK = true;
            }
        }
        if (!$hasPK) {
            array_unshift($columnsDDL, '`' . Model::PK . '` ' . $this->createColumnDDL(['type' => 'pk']));
            $indexesDDL[] = 'PRIMARY KEY (`' . Model::PK . '`)';
        }

        foreach ($indexes as $name => $options) {
            $indexesDDL[] = $this->createIndexDDL($name, $options);
        }

        $sql .= ' ( ' .
            implode(', ', $columnsDDL) . ', ' .
            implode(', ', $indexesDDL) .
            ' )';
        $connection->execute($sql);

    }

    public function existsTable(Connection $connection, $tableName)
    {
        $sql = 'SHOW TABLES LIKE \'' . $tableName . '\'';
        $result = $connection->query($sql);
        return 0 != count($result->fetchAll());
    }

    public function renameTable(Connection $connection, $tableName, $tableNewName)
    {
        $sql = 'RENAME TABLE `' . $tableName . '` TO `' . $tableNewName . '`';
        $connection->execute($sql);
    }

    public function truncateTable(Connection $connection, $tableName)
    {
        $connection->execute('TRUNCATE TABLE `' . $tableName . '`');
    }

    public function dropTable(Connection $connection, $tableName)
    {
        $connection->execute('DROP TABLE `' . $tableName . '`');
    }

    public function addColumn(Connection $connection, $tableName, array $columns)
    {
        $sql = 'ALTER TABLE `' . $tableName . '`';
        $columnsDDL = [];
        foreach ($columns as $name => $options) {
            $columnsDDL[] = 'ADD COLUMN `' . $name . '` ' . $this->createColumnDDL($options);
        }
        $sql .= ' ' .
            implode(', ', $columnsDDL) .
            '';
        $connection->execute($sql);
    }

    public function dropColumn(Connection $connection, $tableName, array $columns)
    {
        $sql = 'ALTER TABLE `' . $tableName . '`';
        $columnsDDL = [];
        foreach ($columns as $name) {
            $columnsDDL[] = 'DROP COLUMN `' . $name . '`';
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
        $sql = 'ALTER TABLE `' . $tableName . '`';
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

    public function findAll($class, $options = [])
    {
        $query = new QueryBuilder();
        $query
            ->select('*')
            ->from($class::getTableName())
            ->where(!empty($options['where']) ? $options['where'] : '')
            ->order(!empty($options['order']) ? $options['order'] : '')
            ->limit(!empty($options['limit']) ? $options['limit'] : '')
            ->params(!empty($options['params']) ? $options['params'] : []);

        $result = $class::getDbConnection()->query($query->getQuery(), $query->getParams())->fetchAll(\PDO::FETCH_CLASS, $class);
        if (!empty($result)) {
            $ret = new Collection($result);
            $ret->setNew(false);
        } else {
            $ret = new Collection();
        }
        return $ret;
    }

    // TODO: полноценная реализация options, сейчас фактически только order
    public function findAllByColumn($class, $column, $value, $options = [])
    {
        $query = new QueryBuilder();
        $query
            ->select('*')
            ->from($class::getTableName())
            ->where('`' . $column . '`=:value' . (!empty($options['where']) ? ' AND (' . $options['where'] . ')' : ''))
            ->order(!empty($options['order']) ? $options['order'] : '')
            ->limit(!empty($options['limit']) ? $options['limit'] : '')
            ->params([':value' => $value]);
        $result = $class::getDbConnection()->query($query->getQuery(), $query->getParams())->fetchAll(\PDO::FETCH_CLASS, $class);
        if (!empty($result)) {
            $ret = new Collection($result);
            $ret->setNew(false);
        } else {
            $ret = new Collection();
        }
        return $ret;
    }

    // TODO: полноценная реализация options, сейчас фактически только order
    public function findByColumn($class, $column, $value, $options = [])
    {
        $query = new QueryBuilder();
        $query
            ->select('*')
            ->from($class::getTableName())
            ->where('`' . $column . '`=:value')
            ->order(!empty($options['order']) ? $options['order'] : '')
            ->limit(1)
            ->params([':value' => $value]);

        $result = $class::getDbConnection()->query($query->getQuery(), $query->getParams())->fetchObject($class);;
        if (!empty($result))
            $result->setNew(false);
        return $result;
    }

    public function countAll($class, $options = [])
    {
        $query = new QueryBuilder();
        $query
            ->select('COUNT(*)')
            ->from($class::getTableName())
            ->where(!empty($options['where']) ? $options['where'] : '')
            ->params(!empty($options['params']) ? $options['params'] : []);

        return $class::getDbConnection()->query($query->getQuery(), $query->getParams())->fetchScalar();
    }

    public function countAllByColumn($class, $column, $value, $options = [])
    {
        $query = new QueryBuilder();
        $query
            ->select('COUNT(*)')
            ->from($class::getTableName())
            ->where('`' . $column . '`=:value')
            ->params([':value' => $value]);

        return $class::getDbConnection()->query($query->getQuery(), $query->getParams())->fetchScalar();
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
                $sets[] = '`' . $column . '`=:' . $column;
                $data[':'.$column] = $model->{$column};
            } elseif (isset($def['default'])) {
                $sets[] = '`' . $column . '`=:' . $column;
                $data[':'.$column] = $def['default'];
            }
        }

        foreach ($relations as $rel => $def) {
            switch ($def['type']) {
                case $class::HAS_ONE:
                case $class::BELONGS_TO:
                    $column = $class::getRelationLinkName($def);
                    if (!in_array($column, $cols)) {
                        if (isset($model->{$column}) && !is_null($model->{$column})) {
                            $sets[] = '`' . $column . '`=:' . $column;
                            $data[':'.$column] = $model->{$column};
                        } elseif (isset($model->{$rel}) && $model->{$rel} instanceof Model) {
                            $sets[] = '`' . $column . '`=:' . $column;
                            $data[':'.$column] = $model->{$rel}->getPk();
                        }
                    }
                    break;
            }
        }

        $connection = $class::getDbConnection();
        if ($model->isNew()) {
            $sql = '
                INSERT INTO `' . $class::getTableName() . '`
                SET ' . implode(', ', $sets) . '
            ';
            $connection->execute($sql, $data);
            $model->{$class::PK} = $connection->lastInsertId();
        } else {
            $sql = '
                UPDATE `' . $class::getTableName() . '`
                SET ' . implode(', ', $sets) . '
                WHERE `' . $class::PK . '`=\'' . $model->{$class::PK} . '\'
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
                case $class::HAS_ONE:
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