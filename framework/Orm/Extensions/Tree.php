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
         */
        if ( !empty($model->__parent) ) {
            $parent = $class::findByPk($model->__parent);
            if (empty($parent))
                return false;
            if ($parent->{$class::PK} == $model->{$class::PK})
                return false;
        }

        /**
         * Вставка новой записи в таблицу
         * ID родительской записи определяем по полю __prt
         */
        if ($model->isNew()) {

            /*
             * Запись вставляется, как новый корень дерева
             */
            if ( !isset($parent) ) {
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

            $result = $connection->execute("
                UPDATE `" . $tableName . "`
                SET
                    `__rgt`=__rgt+2,
                    `__lft`=IF(`__lft`>:rgt, `__lft` + 2, `__lft`) WHERE `__rgt`>=:rgt
                ", [':rgt' => $rgt]);
            if (!$result) {
                return false;
            } else {
                $model->__lft = $rgt;
                $model->__rgt = $rgt + 1;
                $model->__lvl = $lvl + 1;
                $model->__prt = $model->__parent;
                $model->__parent = null;
                return true;
            }

            /**
             * Перенос в дереве уже существующей записи
             */
        } else {

            /**
             * Метод setParent не вызывался
             * либо родительский узел не найден в БД
             * либо родитель не изменился
             * - перемещение не требуется
             */
            // TODO: не рассмотрен случай изменения порядка без смены родительского узла
            if ( !isset($model->__parent) || !isset($parent) || $parent->{$class::PK}==$model->__prt ) {
                return true;
            }

            $lft = $model->__lft;
            $rgt = $model->__rgt;
            $lvl = $model->__lvl;
            if (isset($parent)) {
                $lvlUp = $parent->__lvl;
            } else {
                $lvlUp = 0;
            }

            /*
             * Перенос узла в корень дерева
             */
            if (0 == $model->__parent) {
                $query = new QueryBuilder();
                $query->select('MAX(__rgt)')->from($tableName);
                $rgtKeyNear = (int)$connection->query($query->getQuery())->fetchScalar();

            } else {
                /*
                 * Простое перемещение в другой узел
                 */
                if ( true ) {
                    $rgtKeyNear = $parent->__rgt - 1;
                    /*
                     * Поднятие узла в той же ветке на уровень выше
                     */
                } else {
                    //Правый ключ старого родительского узла??
                    //$rgtKeyNear =
                }
            }

            $skewLvl = $lvlUp - $lvl + 1;
            $skewTree = $rgt - $lft + 1;

            /*
             * PK узлов перемещаемой ветки
             */
            $ids = $connection->query("
                    SELECT `" . $class::PK . "`
                    FROM `" . $tableName . "` AS t WHERE t.__lft >= :lft AND t.__rgt <= :rgt
                ", [':lft'=>$lft, ':rgt'=>$rgt])->fetchAll(\PDO::FETCH_COLUMN);

            /*
             * Перемещение в область вышестоящих узлов
             */
            if ($rgtKeyNear > $rgt) {

                $skewEdit = $rgtKeyNear - $lft;
                /*
                $sql = "
                UPDATE `" . $tableName . "`
                SET
                  __rgt = IF(__lft >= :lft, __rgt + :skewEdit, IF(__rgt < :lft, __rgt + :skewTree, __rgt)),
                  __lvl = IF(__lft >= :lft, __lvl + :skewLvl, __lvl),
                  __lft = IF(__lft >= :lft, __lft + :skewEdit, IF(__lft > :rgtKeyNear, __lft + :skewTree, __lft))
                WHERE __rgt > :rgtKeyNear AND __lft < :rgt
                ";
                */
                $sql = "UPDATE `" . $tableName . "`
                        SET __rgt = :rgt + :skewTree
                        WHERE __rgt < :lft AND __rgt > :rgtKeyNear";
                $result = $connection->execute($sql, [
                    ':lft'=>$lft, ':rgt'=>$rgt,
                    ':rgtKeyNear'=>$rgtKeyNear,
                    ':skewTree'=>$skewTree,
                ]);

                $sql = "UPDATE `" . $tableName . "`
                        SET __lft = __lft + :skewTree
                        WHERE __lft < :lft AND __lft > :rgtKeyNear";
                $result = $result && $connection->execute($sql, [
                        ':lft'=>$lft,
                        ':rgtKeyNear'=>$rgtKeyNear,
                        ':skewTree'=>$skewTree,
                    ]);

                $sql = "UPDATE `" . $tableName . "`
                        SET __lft = __lft + :skewEdit, __rgt = __rgt + :skewEdit, __lvl = __lvl + :skewLvl
                        WHERE `" . $class::PK . "` IN (" . implode(',', array_unique($ids)) . ")";
                $result = $result && $connection->execute($sql, [
                        ':skewEdit'=>$skewEdit, ':skewLvl'=>$skewLvl,
                    ]);

                if ($result) {
                    $model->__lft = $model->__lft + $skewEdit;
                    $model->__rgt = $model->__rgt + $skewEdit;
                    $model->__lvl = $model->__lvl + $skewLvl;
                    $model->__prt = $model->__parent;
                }

                /*
                 * Перемещение в область нижестоящих узлов
                 */
            } else {

                $skewEdit = $rgtKeyNear - $lft + 1 - $skewTree;

            }

            return $result;

        }

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
                    $model->__parent = $argv[0]->__prt;
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