<?php

namespace T4\Orm\Extensions;

use T4\Dbal\Connection;
use T4\Dbal\QueryBuilder;
use T4\Orm\Model;

trait TTreeService
{

    /**
     * "Удаление" из дерева элементов в заданном диапазоне (включительно) с "закрытием" дыры
     * @param \T4\Dbal\Connection $connection
     * @param string $table
     * @param int $lft
     * @param int $rgt
     */
    protected function removeFromTreeByLftRgt(Connection $connection, $table, $lft, $rgt)
    {
        $query = new QueryBuilder();
        $query
            ->update()
            ->table($table)
            ->values([
                '__lft' => 'CASE WHEN __lft>:rgt THEN __lft - (:width + 1) ELSE __lft END',
                '__rgt' => 'CASE WHEN __rgt>:rgt THEN __rgt - (:width + 1) ELSE __rgt END',
            ]);
        $query->params([':rgt' => $rgt, ':width' => $rgt - $lft]);
        $connection->execute($query);
    }

    /**
     * "Удаление" из дерева заданного элемента и всех его детей
     * Физического удаления не происходит, лишь "смыкаются" индексы других элементов
     * @param \T4\Orm\Model $element
     */
    protected function removeFromTreeByElement(Model $element)
    {
        /** @var \T4\Orm\Model $class */
        $class = get_class($element);
        $tableName = $class::getTableName();
        /** @var \T4\Dbal\Connection $connection */
        $connection = $class::getDbConnection();

        $this->removeFromTreeByLftRgt($connection, $tableName, $element->__lft, $element->__rgt);
    }

    /**
     * Создает пустое место в дереве заданной ширины
     * Место создается ПЕРЕД элементом с заданным lft
     * @param \T4\Dbal\Connection $connection
     * @param string $table
     * @param int $lft
     * @param int $width
     */
    protected function expandTreeBeforeLft(Connection $connection, $table, $lft, $width)
    {
        $query = new QueryBuilder();
        $query
            ->update()
            ->table($table)
            ->values([
                '__lft' => 'CASE WHEN __lft>=:lft THEN __lft + (:width + 1) ELSE __lft END',
                '__rgt' => 'CASE WHEN __rgt>=:lft THEN __rgt + (:width + 1) ELSE  __rgt END',
            ])
            ->where('__lft>=:lft OR __rgt>=:lft')
            ->order('__lft DESC');
        $query->params([':lft' => $lft, ':width' => $width]);
        $connection->execute($query);
    }

    /**
     * Создает пустое место в дереве заданной ширины
     * Место создается ВНУТРИ элемента с заданным lft как ПЕРВЫЙ его потомок
     * @param \T4\Dbal\Connection $connection
     * @param string $table
     * @param int $lft
     * @param int $width
     */
    protected function expandTreeAfterLft(Connection $connection, $table, $lft, $width)
    {
        $query = new QueryBuilder();
        $query
            ->update()
            ->table($table)
            ->values([
                '__lft' => 'CASE WHEN __lft>:lft THEN __lft + (:width + 1) ELSE __lft END',
                '__rgt' => 'CASE WHEN __rgt>=:lft THEN __rgt + (:width + 1) ELSE __rgt END',
            ])
            ->where('__lft>:lft OR __rgt>=:lft')
            ->order('__lft DESC');
        $query->params([':lft' => $lft, ':width' => $width]);
        $connection->execute($query);
    }

    /**
     * Создает пустое место в дереве заданной ширины
     * Место создается ПЕРЕД заданным элементом
     * @param Model $element
     * @param $width
     */
    protected function expandTreeBeforeElement($element, $width)
    {
        /** @var \T4\Orm\Model $class */
        $class = get_class($element);
        $tableName = $class::getTableName();
        /** @var \T4\Dbal\Connection $connection */
        $connection = $class::getDbConnection();
        $this->expandTreeBeforeLft($connection, $tableName, $element->__lft, $width);
    }

    /**
     * Создает пустое место в дереве заданной ширины
     * Место создается ВНУТРИ заданного элемента как ПЕРВЫЙ его потомок
     * @param Model $element
     * @param $width
     */
    protected function expandTreeInElementFirst($element, $width)
    {
        /** @var \T4\Orm\Model $class */
        $class = get_class($element);
        $tableName = $class::getTableName();
        /** @var \T4\Dbal\Connection $connection */
        $connection = $class::getDbConnection();

        $this->expandTreeAfterLft($connection, $tableName, $element->__lft, $width);
    }

    /**
     * Создает пустое место в дереве заданной ширины
     * Место создается ПОСЛЕ элемента с заданным rgt
     * @param \T4\Dbal\Connection $connection
     * @param string $table
     * @param int $rgt
     * @param int $width
     */
    protected function expandTreeAfterRgt(Connection $connection, $table, $rgt, $width)
    {
        $query = new QueryBuilder();
        $query
            ->update()
            ->table($table)
            ->values([
                '__lft' => 'CASE WHEN __lft>:rgt THEN __lft + (:width + 1) ELSE __lft END',
                '__rgt' => 'CASE WHEN __rgt>:rgt THEN __rgt + (:width + 1) ELSE __rgt END',
            ])
            ->where('__lft>:rgt OR __rgt>:rgt')
            ->order('__lft DESC');
        $query->params([':rgt' => $rgt, ':width' => $width]);
        $connection->execute($query);
    }

    /**
     * Создает пустое место в дереве заданной ширины
     * Место создается ВНУТРИ элемента с заданным rgt как ПОСЛЕДНИЙ его потомок
     * @param \T4\Dbal\Connection $connection
     * @param string $table
     * @param int $rgt
     * @param int $width
     */
    protected function expandTreeBeforeRgt(Connection $connection, $table, $rgt, $width)
    {
        $query = new QueryBuilder();
        $query
            ->update()
            ->table($table)
            ->values([
                '__lft' => 'CASE WHEN __lft>=:rgt THEN __lft + (:width + 1) ELSE __lft END',
                '__rgt' => 'CASE WHEN __rgt>=:rgt THEN __rgt + (:width + 1) ELSE __rgt END',
            ])
            ->where('__lft>=:rgt OR __rgt>=:rgt')
            ->order('__lft DESC');
        $query->params([':rgt' => $rgt, ':width' => $width]);
        $connection->execute($query);
    }

    /**
     * Создает пустое место в дереве заданной ширины
     * Место создается ПОСЛЕ заданного элемента
     * @param Model $element
     * @param $width
     */
    protected function expandTreeAfterElement($element, $width)
    {
        /** @var \T4\Orm\Model $class */
        $class = get_class($element);
        $tableName = $class::getTableName();
        /** @var \T4\Dbal\Connection $connection */
        $connection = $class::getDbConnection();
        $this->expandTreeAfterRgt($connection, $tableName, $element->__rgt, $width);
    }

    /**
     * Создает пустое место в дереве заданной ширины
     * Место создается ВНУТРИ заданного элемента как ПЕРВЫЙ его потомок
     * @param Model $element
     * @param $width
     */
    protected function expandTreeInElementLast($element, $width)
    {
        /** @var \T4\Orm\Model $class */
        $class = get_class($element);
        $tableName = $class::getTableName();
        /** @var \T4\Dbal\Connection $connection */
        $connection = $class::getDbConnection();
        $this->expandTreeBeforeRgt($connection, $tableName, $element->__rgt, $width);
    }

}