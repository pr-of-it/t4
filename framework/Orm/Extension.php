<?php

namespace T4\Orm;

use T4\Orm\Extensions\Exception;

/**
 * Абстрактный класс расширения модели
 * Class Extension
 * @package T4\Orm
 */
abstract class Extension
{

    /**
     * Функция изменения состава полей модели
     * @param array $columns
     * @param string $class
     * @return array
     */
    public function prepareColumns($columns, $class = '')
    {
        return $columns;
    }

    /**
     * Функция изменения состава индексов модели
     * @param array $indexes
     * @param string $class
     * @return array
     */
    public function prepareIndexes($indexes, $class = '')
    {
        return $indexes;
    }

    /**
     * Функция изменения состава связей модели
     * @param $relations array
     * @param $class
     * @return array
     */
    public function prepareRelations($relations, $class = '')
    {
        return $relations;
    }

    /**
     * @param $model \T4\Orm\Model
     * @return bool
     */
    public function afterFind(Model &$model)
    {
        return true;
    }

    /**
     * Метод, срабатывающий перед сохранением модели в БД
     * Возврат false предотвращает сохранение
     * @param $model \T4\Orm\Model
     * @return bool
     */
    public function beforeSave(Model &$model)
    {
        return true;
    }

    /**
     * Метод, срабатывающий после сохранения модели в БД
     * @param $model \T4\Orm\Model
     * @return bool
     */
    public function afterSave(Model &$model)
    {
        return true;
    }

    /**
     * Метод, срабатывающий перед удалением модели из БД
     * Возврат false предотвращает удаление
     * @param $model \T4\Orm\Model
     * @return bool
     */
    public function beforeDelete(Model &$model)
    {
        return true;
    }

    /**
     * Метод, срабатывающий после удаления модели в БД
     * @param $model \T4\Orm\Model
     * @return bool
     */
    public function afterDelete(Model &$model)
    {
        return true;
    }

    public static function hasMagicStaticMethod($method)
    {
        return false;
    }

    public function hasMagicDynamicMethod($method)
    {
        return false;
    }

}