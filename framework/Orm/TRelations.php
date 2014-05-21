<?php

namespace T4\Orm;

use T4\Core\Collection;
use T4\Dbal\QueryBuilder;

trait TRelations {

    /**
     * Список связей модели
     * @return array
     */
    public static function getRelations() {
        $schema = static::getSchema();
        return !empty($schema['relations']) ? $schema['relations'] : [];
    }

    /**
     * Возвращает имя поля связи
     * @param array $relation
     * @return string
     */
    public static function getRelationLinkName($relation)
    {
        if (!empty($relation['on']))
            return $relation['on'];

        $class = get_called_class();
        switch ($relation['type']) {
            case $class::HAS_ONE:
            case $class::BELONGS_TO:
                $class = explode('\\', $relation['model']);
                $class = array_pop($class);
                return '__' . strtolower($class) . '_id';
            case $class::HAS_MANY:
                $class = explode('\\', $class);
                $class = array_pop($class);
                return '__' . strtolower($class) . '_id';
            case $class::MANY_TO_MANY:
                $thisTableName = $class::getTableName();
                $relationClass = (false !== strpos($relation['model'], 'App\\')) ? $relation['model'] : '\\App\\Models\\' . $relation['model'];
                $thatTableName = $relationClass::getTableName();
                return $thisTableName < $thatTableName ? $thisTableName .'_to_'. $thatTableName : $thatTableName .'_to_'. $thisTableName;
        }

    }

    public static function getManyToManyThisLinkColumnName()
    {
        $class = get_called_class();
        $class = explode('\\', $class);
        $class = array_pop($class);
        return '__' . strtolower($class) . '_id';
    }

    public static function getManyToManyThatLinkColumnName($relation)
    {
        $class = explode('\\', $relation['model']);
        $class = array_pop($class);
        return '__' . strtolower($class) . '_id';
    }

    /**
     * "Ленивое" получение данных связи для моделей
     * @param string $key
     * @return mixed
     * @throws Exception
     */
    protected function getRelationLazy($key)
    {
        $class = get_class($this);
        $relations = $class::getRelations();
        if (empty($relations[$key]))
            throw new Exception('No such column or relation: ' . $key . ' in model of ' . $class . ' class');

        $relation = $relations[$key];
        switch ($relation['type']) {

            case $class::HAS_ONE:
            case $class::BELONGS_TO:
                $relationClass = (false !== strpos($relation['model'], 'App\\')) ? $relation['model'] : '\\App\\Models\\' . $relation['model'];
                $link = $class::getRelationLinkName($relation);
                $subModel = $relationClass::findByPK($this->{$link});
                if (empty($subModel))
                    return null;
                else
                    return $relationClass::findByPK($this->{$link});
                break;

            case $class::HAS_MANY:
                $relationClass = (false !== strpos($relation['model'], 'App\\')) ? $relation['model'] : '\\App\\Models\\' . $relation['model'];
                $link = $class::getRelationLinkName($relation);
                return $relationClass::findAllByColumn($link, $this->getPk());
                break;

            case $class::MANY_TO_MANY:
                $relationClass = (false !== strpos($relation['model'], 'App\\')) ? $relation['model'] : '\\App\\Models\\' . $relation['model'];
                $linkTable = $class::getRelationLinkName($relation);
                $query = new QueryBuilder();
                $query
                    ->select('t1.*')
                    ->from($relationClass::getTableName())
                    ->rightJoin($linkTable, 't1.' . $relationClass::PK . '=j1.' . static::getManyToManyThatLinkColumnName($relation))
                    ->where('j1.'.static::getManyToManyThisLinkColumnName().'=:id');
                $query->params([':id'=>$this->getPk()]);
                $result = $relationClass::getDbConnection()->query($query->getQuery(), $query->getParams())->fetchAll(\PDO::FETCH_CLASS, $relationClass);
                if (!empty($result)) {
                    $ret = new Collection($result);
                    $ret->setNew(false);
                    return $ret;
                } else {
                    return new Collection();
                }

        }
    }

    protected function setRelation($key, $value)
    {
        $class = get_class($this);
        $relations = $class::getRelations();
        if (empty($relations[$key])) {
            throw new Exception('No such relation: ' . $key . ' in model of ' . $class . ' class');
        }

        $relation = $relations[$key];
        switch ($relation['type']) {

            case $class::HAS_ONE:
            case $class::BELONGS_TO:
                $relationClass = (false !== strpos($relation['model'], 'App\\')) ? $relation['model'] : '\\App\\Models\\' . $relation['model'];
                if ($value instanceof $relationClass) {
                    $this->$key = $value;
                } else {
                    $this->$key = $relationClass::findByPk($value);
                }
                break;

            default:
                $this->$key = $value;
                break;

        }

    }

}