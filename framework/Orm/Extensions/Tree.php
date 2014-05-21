<?php

namespace T4\Orm\Extensions;

use T4\Dbal\QueryBuilder;
use T4\Orm\Extension;
use T4\Orm\Model;

class Tree
    extends Extension
{

    public function prepareColumns($columns, $class='')
    {
        return $columns + [
            '__lft' => ['type' => 'int'],
            '__rgt' => ['type' => 'int'],
            '__lvl' => ['type' => 'int'],
            '__prt' => ['type' => 'link'],
        ];
    }

    public function prepareIndexes($indexes, $class='')
    {
        return $indexes + [
            '__lft' => ['columns' => ['__lft']],
            '__rgt' => ['columns' => ['__rgt']],
            '__lvl' => ['columns' => ['__lvl']],
            '__key' => ['columns' => ['__lft', '__rgt', '__lvl']],
            '__prt' => ['columns' => ['__prt']],
        ];
    }

    public function prepareRelations($relations, $class='')
    {
        return $relations + [
            'parent' => [
                'type' => Model::BELONGS_TO,
                'model' => $class,
                'on' => '__prt',
            ],
            /*
            // TODO: сделать и проверить!
            'children' => [
                'type' => Model::HAS_MANY,
                'model' => $class,
                'on' => '__prt',
            ]
            */
        ];
    }

    public function beforeSave(&$model)
    {

        /** @var \T4\Orm\Model $class */
        $class = get_class($model);
        $tableName = $class::getTableName();
        /** @var \T4\Dbal\Connection $connection */
        $connection = $class::getDbConnection();

        if (empty($model->parent)) {
            $parentId = 0;
        } else {
            $parent = $model->parent;
            $parentId = $model->parent->getPk();
        }

        /**
         * Вставка новой записи в таблицу
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

            $skewTree = $model->__rgt - $model->__lft + 1;

            if (isset($parent)) {
                $lft = $parent->__rgt;
                $lvl = $parent->__lvl + 1;
            } else {
                $query = new QueryBuilder();
                $query->select('MAX(__rgt)')->from($tableName);
                $lft = (int)$connection->query($query->getQuery())->fetchScalar() + 1;
                $lvl = 0;
            }

            // Перемещение в диапазон перемещаемого узла запрещено!
            if ( $lft>0 && $lft>$model->__lft && $lft<=$model->__rgt) {
                return false;
            }

            $skewLvl = $lvl - $model->__lvl;

            if ($lft > $model->__lft) {
                /*
                 * Перемещение вверх по дереву
                 */
                $skewEdit = $lft - $model->__lft - $skewTree;

                $sql = "
                UPDATE `" . $tableName . "`
                SET __lft = CASE WHEN __rgt <= " . $model->__rgt . "
                                     THEN __lft + :skewEdit
                                     ELSE CASE WHEN __lft > " . $model->__rgt . "
                                               THEN __lft - :skewTree
                                               ELSE __lft
                                          END
                               END,
                    __lvl =  CASE WHEN __rgt <= " . $model->__rgt . "
                                    THEN __lvl + :skewLvl
                                    ELSE __lvl
                               END,
                    __rgt = CASE WHEN __rgt <= " . $model->__rgt . "
                                     THEN __rgt + :skewEdit
                                     ELSE CASE WHEN __rgt < :lft
                                               THEN __rgt - :skewTree
                                               ELSE __rgt
                                          END
                                END
                WHERE __rgt > " . $model->__lft . " AND
                      __lft < :lft
                ";
                $result = $connection->query($sql, [
                    ':skewTree'=>$skewTree, ':skewLvl'=>$skewLvl, ':skewEdit'=>$skewEdit,
                    ':lft' => $lft,
                ]);
                if (!$result)
                    return false;
                $lft = $lft - $skewTree;

            } else {
                /*
                 * Перемещение вниз по дереву
                 */
                $skewEdit = $lft - $model->__lft;

                $sql = "
                UPDATE `" . $tableName . "`
                    SET
                        __rgt = CASE WHEN __lft >= " . $model->__lft . "
                                         THEN __rgt + :skewEdit
                                         ELSE CASE WHEN __rgt < " . $model->__lft . "
                                                   THEN __rgt + :skewTree
                                                   ELSE __rgt
                                              END
                                    END,
                        __lvl = CASE WHEN __lft >= " . $model->__lft . "
                                         THEN __lvl + :skewLvl
                                         ELSE __lvl
                                    END,
                        __lft =  CASE WHEN __lft >= " . $model->__lft . "
                                         THEN __lft + :skewEdit
                                         ELSE CASE WHEN __lft >= :lft
                                                   THEN __lft + :skewTree
                                                   ELSE __lft
                                              END
                                    END
                    WHERE __rgt >= :lft AND
                          __lft < " . $model->__rgt . "
                ";
                $result = $connection->query($sql, [
                    ':skewTree'=>$skewTree, ':skewLvl'=>$skewLvl, ':skewEdit'=>$skewEdit,
                    ':lft' => $lft,
                ]);
                if (!$result)
                    return false;

            }

            $model->__lft = $lft;
            $model->__lvl = $lvl;
            $model->__rgt = $lft + $skewTree - 1;
            $model->__prt = $parentId;

            return true;

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
        /**
         * @var \T4\Orm\Model $class
         */
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
        switch ($method) {
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