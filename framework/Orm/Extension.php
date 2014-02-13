<?php

namespace T4\Orm;

/**
 * Абстрактный класс расширения модели
 * Class Extension
 * @package T4\Orm
 */
abstract class Extension
{

    /**
     * Функция изменения состава полей модели
     * @param $columns array
     * @return array
     */
    public function prepareColumns($columns)
    {
        return $columns;
    }

    /**
     * Функция изменения состава индексов модели
     * @param $indexes array
     * @return array
     */
    public function prepareIndexes($indexes)
    {
        return $indexes;
    }

    /**
     * Метод, срабатывающий перед сохранением модели в БД
     * Возврат false предотвращает сохранение
     * @param $model \T4\Orm\Model
     * @return bool
     */
    public function beforeSave(&$model)
    {
        return true;
    }

    /**
     * Метод, срабатывающий после сохранения модели в БД
     * @param $model \T4\Orm\Model
     * @return bool
     */
    public function afterSave(&$model)
    {
        return true;
    }

    /**
     * Метод, срабатывающий перед удалением модели из БД
     * Возврат false предотвращает удаление
     * @param $model \T4\Orm\Model
     * @return bool
     */
    public function beforeDelete(&$model)
    {
        return true;
    }

    /**
     * Метод, срабатывающий после удаления модели в БД
     * @param $model \T4\Orm\Model
     * @return bool
     */
    public function afterDelete(&$model)
    {
        return true;
    }

    /**
     * Метод-коллекция статических методов, добавляемых к модели
     * Должен выбрасывать исключение, если в данном расширении указанный метод не найден
     * @param $class string
     * @param $method string
     * @param $argv array
     * @throws \T4\Orm\Exception
     */
    public function callStatic($class, $method, $argv)
    {
        throw new Exception('Method ' . $method . ' is not found in extension ' . get_called_class());
    }

    /**
     * Метод-коллекция динамических методов, добавляемых к модели
     * Должен выбрасывать исключение, если в данном расширении указанный метод не найден
     * @param $model \T4\Orm\Model
     * @param $method string
     * @param $argv array
     * @throws \T4\Orm\Exception
     */
    public function call(&$model, $method, $argv)
    {
        throw new Exception('Method ' . $method . ' is not found in extension ' . get_called_class());
    }

}