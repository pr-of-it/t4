<?php

namespace T4\Orm;

trait TRelations {

    /**
     * Список связей модели
     * @return array
     */
    public static function getRelations() {
        $schema = static::getSchema();
        return $schema['relations'];
    }

    /**
     * Возвращает имя поля связи
     * @param string $class
     * @param array $relation
     * @return string
     */
    protected static function getRelationLinkColumn($class, $relation)
    {
        switch ($relation['type']) {
            case $class::HAS_ONE:
            case $class::BELONGS_TO:
                return '__' . strtolower($relation['model']) . '_id';
            case $class::HAS_MANY:
                $class = explode('\\', $class);
                $class = array_pop($class);
                return '__' . strtolower($class) . '_id';
        }

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
                $relationClass = '\\App\\Models\\' . $relation['model'];
                $link = $this->getRelationLinkColumn($class, $relation);
                return $relationClass::findByPK($this->{$link});
                break;
            case $class::HAS_MANY:
                $relationClass = '\\App\\Models\\' . $relation['model'];
                $link = $this->getRelationLinkColumn($class, $relation);
                return $relationClass::findAllByColumn($link, $this->getPk());
                break;
        }
    }

}