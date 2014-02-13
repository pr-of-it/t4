<?php

namespace T4\Orm\Extensions;

use T4\Dbal\QueryBuilder;
use T4\Orm\Exception;
use T4\Orm\Extension;
use T4\Orm\Model;

class Tree
    extends Extension
{

    public function prepareColumns($columns)
    {
        return $columns + [
            '__lft' => ['type' => 'int'],
            '__rgt' => ['type' => 'int'],
            '__lvl' => ['type' => 'int'],
            '__prt' => ['type' => 'link'],
        ];
    }

    public function prepareIndexes($indexes)
    {
        return $indexes + [
            '__lft' => ['columns' => ['__lft']],
            '__rgt' => ['columns' => ['__rgt']],
            '__lvl' => ['columns' => ['__lvl']],
            '__key' => ['columns' => ['__lft', '__rgt', '__lvl']],
            '__prt' => ['columns' => ['__prt']],
        ];
    }

    public function beforeSave(&$model)
    {

        $class = get_class($model);
        $tableName = $class::getTableName();
        /** @var $connection \T4\Dbal\Connection */
        $connection = $class::getDbConnection();

        /**
         * Был вызван метод setParent(array|Model $parent)
         * Находим родительский узел в БД
         * Проверяем его существование
         * и то, не равен ли он данному (замыкание на себя)
         * На выходе имеем $parentId (возможно =0) и $parent (возможно не определено)
         */
        if ( !empty($model->__parent) ) {
            $parent = $class::findByPk($model->__parent);
            if (empty($parent))
                return false;
            if (!$model->isNew() && $parent->{$class::PK} == $model->{$class::PK})
                return false;
            $parentId = (int)$parent->{$class::PK};
        } else {
            $parentId = 0;
        }

        /**
         * Вставка новой записи в таблицу
         * ID родительской записи определяем по полю __prt
         */
        if ($model->isNew()) {

            /*
             * Запись вставляется, как новый корень дерева
             */
            if ( 0 == $parentId ) {
                $query = new QueryBuilder();
                $query->select('MAX(__rgt)')->from($tableName);
                $rgt = (int)$connection->query($query->getQuery())->fetchScalar() + 1;
                $lvl = 0;
            /*
             * У записи будет существующий родитель
             */
            } else {
                $rgt = $parent->__rgt;
                $lvl = $parent->__lvl;
            }

            $model->__lft = $rgt;
            $model->__rgt = $rgt + 1;
            $model->__lvl = $lvl + 1;
            $model->__prt = $parentId;
            $model->__parent = null;

            $sql = "UPDATE `" . $tableName . "`
                    SET
                    `__rgt`=__rgt+2,
                    `__lft` = IF( `__lft`>:rgt, `__lft` + 2, `__lft` )
                    WHERE `__rgt`>=:rgt
                    ";
            $result = $connection->execute($sql, [':rgt' => $rgt]);
            return $result;

        /**
         * Перенос в дереве уже существующей записи
         */
        } else {

            die('ERROR!');

        }

    }

    /**
     * Удаление узла дерева
     * В данном методе удаляются все оставшиеся подузлы
     * @param \T4\Orm\Model $model
     * @return bool
     */
    public function afterDelete(&$model)
    {
        $class = get_class($model);
        $tableName = $class::getTableName();
        /** @var $connection \T4\Dbal\Connection */
        $connection = $class::getDbConnection();

        $sql = "
            DELETE FROM `" . $tableName . "`
            WHERE __lft >= :lft AND __rgt <= :rgt
            ";
        $result = $connection->execute($sql, [':lft' => $model->__lft, ':rgt' => $model->__rgt]);

        $sql = "
            UPDATE `" . $tableName . "`
                SET __lft = IF(__lft > :lft, __lft - (:rgt - :lft + 1), __lft),
                __rgt = __rgt - (:rgt - :lft + 1)
            WHERE __rgt > :rgt
            ";
        $result = $result && $connection->execute($sql, [':lft' => $model->__lft, ':rgt' => $model->__rgt]);

        return $result;
    }

    public function callStatic($class, $method, $argv)
    {
        switch (true) {
            case 'findAllTree' == $method:
                return $class::findAll(['order'=>'__lft']);
                break;
        }
        throw new Exception('Method ' . $method . ' is not found in extension ' . __CLASS__);
    }

    public function call(&$model, $method, $argv)
    {
        $class = get_class($model);
        switch (true) {
            // TODO: убрать отсюда с появлением relations
            case 'setParent':
                if ( is_numeric($argv[0]) ) {
                    $model->__parent = (int)$argv[0];
                } elseif ( $argv[0] instanceof Model) {
                    $class = get_class($model);
                    $model->__parent = $argv[0]->{$class::PK};
                }
                return $model;
            case 'findAllChildren':
                return $class::findAll([
                    'where'=>'__lft>'.$model->__lft.' AND __rgt<='.$model->__rgt,
                    'order'=>'__lft'
                ]);
            case 'findSubTree':
                return $class::findAll([
                    'where'=>'__lft>='.$model->__lft.' AND __rgt<='.$model->__rgt,
                    'order'=>'__lft'
                ]);
        }
        throw new Exception('Method ' . $method . ' is not found in extension ' . __CLASS__);
    }

}