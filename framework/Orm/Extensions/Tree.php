<?php

namespace T4\Orm\Extensions;

use T4\Core\Collection;
use T4\Dbal\Connection;
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
            'children' => [
                'type' => Model::HAS_MANY,
                'model' => $class,
                'on' => '__prt',
            ],
        ];
    }

    /**
     * Манипуляции с деревом nested sets
     */

    /**
     * "Удаление" из дерева элементов в заданном диапазоне с "закрытием" дыры
     * @param \T4\Dbal\Connection $connection
     * @param string $table
     * @param int $lft
     * @param int $rgt
     */
    protected function removeFromTree(Connection $connection, $table, $lft, $rgt)
    {
        $sql = "
                UPDATE `" . $table . "`
                SET
                    `__lft` = IF(`__lft` > :rgt, `__lft` - (:width + 1), `__lft`),
                    `__rgt` = `__rgt` - (:width + 1)
                WHERE `__rgt` > :rgt
            ";
        $connection->execute($sql, [':rgt' => $rgt, ':width' => $rgt - $lft]);
    }

    /**
     * Подготовка модели к вставке в дерево перед заданным элементом, в то же поддерево, что и заданный элемент
     * @param \T4\Orm\Model $model
     * @param \T4\Orm\Model $element
     * @return bool
     */
    protected function insertModelBeforeElement(Model &$model, Model &$element)
    {
        /** @var \T4\Orm\Model $class */
        $class = get_class($model);
        $tableName = $class::getTableName();
        /** @var \T4\Dbal\Connection $connection */
        $connection = $class::getDbConnection();

        $sql = "
            UPDATE `" . $tableName . "`
            SET
                `__rgt` = `__rgt` + 2,
                `__lft` = `__lft` + 2
            WHERE `__lft`>=:lft
        ";
        $connection->execute($sql, [':lft' => $element->__lft]);

        $model->__lft = $element->__lft;
        $model->__rgt = $element->__lft + 1;
        $model->__lvl = $element->__lvl;
        $model->__prt = $element->__prt;

        $element->__lft += 2;
        $element->__rgt += 2;

    }

    /**
     * Подготовка модели к вставке в дерево сразу после заданного элемента, в то же поддерево, что и заданный элемент
     * @param \T4\Orm\Model $model
     * @param \T4\Orm\Model $element
     * @return bool
     */
    protected function insertModelAfterElement(Model &$model, Model &$element)
    {
        /** @var \T4\Orm\Model $class */
        $class = get_class($model);
        $tableName = $class::getTableName();
        /** @var \T4\Dbal\Connection $connection */
        $connection = $class::getDbConnection();

        $sql = "
            UPDATE `" . $tableName . "`
            SET `__rgt` = `__rgt` + 2
            WHERE `__rgt`>:rgt
        ";
        $connection->execute($sql, [':rgt' => $element->__rgt]);
        $sql = "
            UPDATE `" . $tableName . "`
            SET `__lft` = `__lft` + 2
            WHERE `__lft`>:rgt
        ";
        $connection->execute($sql, [':rgt' => $element->__rgt]);

        $model->__lft = $element->__rgt + 1;
        $model->__rgt = $element->__rgt + 2;
        $model->__lvl = $element->__lvl;
        $model->__prt = $element->__prt;

    }


    /**
     * Подготовка модели к вставке в дерево в качестве первого потомка заданного элемента
     * @param \T4\Orm\Model $model
     * @param \T4\Orm\Model $parent
     * @throws \T4\Orm\Exception
     */
    protected function insertModelAsFirstChildOf(Model &$model, Model &$parent)
    {
        if ($parent->isNew())
            throw new \T4\Orm\Exception('Parent model should be saved before child insert');

        /** @var \T4\Orm\Model $class */
        $class = get_class($model);
        $tableName = $class::getTableName();
        /** @var \T4\Dbal\Connection $connection */
        $connection = $class::getDbConnection();

        $sql = "
            UPDATE `" . $tableName . "`
            SET `__rgt` = `__rgt` + 2
            WHERE `__lft` >= :lft
        ";
        $connection->execute($sql, [':lft' => $parent->__lft]);
        $sql = "
            UPDATE `" . $tableName . "`
            SET `__lft` = `__lft` + 2
            WHERE `__lft` > :lft
        ";
        $connection->execute($sql, [':lft' => $parent->__lft]);

        $model->__lft = $parent->__lft + 1;
        $model->__rgt = $parent->__lft + 2;
        $model->__lvl = $parent->__lvl + 1;
        $model->__prt = $parent->getPk();

    }

    /**
     * Подготовка модели к вставке в дерево в качестве последнего потомка заданного элемента
     * @param \T4\Orm\Model $model
     * @param \T4\Orm\Model $parent
     * @throws \T4\Orm\Exception
     */
    protected function insertModelAsLastChildOf(Model &$model, Model &$parent)
    {

        if ($parent->isNew())
            throw new \T4\Orm\Exception('Parent model should be saved before child insert');

        /** @var \T4\Orm\Model $class */
        $class = get_class($model);
        $tableName = $class::getTableName();
        /** @var \T4\Dbal\Connection $connection */
        $connection = $class::getDbConnection();

        $sql = "
            UPDATE `" . $tableName . "`
            SET `__rgt` = `__rgt` + 2
            WHERE `__rgt` >= :rgt
        ";
        $connection->execute($sql, [':rgt' => $parent->__rgt]);
        $sql = "
            UPDATE `" . $tableName . "`
            SET `__lft` = `__lft` + 2
            WHERE `__lft` > :rgt
        ";
        $connection->execute($sql, [':rgt' => $parent->__rgt]);

        $model->__lft = $parent->__rgt;
        $model->__rgt = $parent->__rgt + 1;
        $model->__lvl = $parent->__lvl + 1;
        $model->__prt = $parent->getPk();

        $parent->__rgt += 2;

    }

    /**
     * Подготовка модели к вставке в дерево в качестве последнего корня
     * @param \T4\Orm\Model $model
     */
    protected function insertModelAsLastRoot(Model &$model)
    {
        /** @var \T4\Orm\Model $class */
        $class = get_class($model);
        $tableName = $class::getTableName();
        /** @var \T4\Dbal\Connection $connection */
        $connection = $class::getDbConnection();

        if (!$model->isNew()) {
            $this->removeFromTree($connection, $tableName, $model->__lft, $model->__rgt);
            $modelWidth = $model->__rgt - $model->__lft;
        } else {
            $modelWidth = 1;
        }

        $query = new QueryBuilder();
        $query->select('MAX(`__rgt`)')->from($tableName);
        $maxRgt = (int)$connection->query($query->getQuery())->fetchScalar();

        $model->__lft = $maxRgt + 1;
        $model->__rgt = $model->__lft + $modelWidth;
        $model->__lvl = 0;
        $model->__prt = 0;

        if (!$model->isNew()) {
            // TODO: move subtree into new place of $model
        }

    }

    /*
     * Методы модели
     */

    public function beforeSave(&$model)
    {

        if ($model->isNew()) {
            if (empty($model->parent)) {
                $this->insertModelAsLastRoot($model);
            } else {
                $this->insertModelAsLastChildOf($model, $model->parent);
            }
            return true;
        } else {
            /** @var \T4\Orm\Model $class */
            $class = get_class($model);
            $oldParent = empty($model->__prt) ? null : $class::findByPk($model->__prt);
            if ($oldParent == $model->parent) {
                return true;
            } else {
                if (empty($model->parent)) {
                    $this->insertModelAsLastRoot($model);
                } else {
                    $this->insertModelAsLastChildOf($model, $model->parent);
                }
                return true;
            }
        }

        if (empty($model->parent)) {
            $parent = null;
            $parentId = 0;
        } else {
            $parent = $model->parent;
            $parentId = $model->parent->getPk();
        }

        /** @var \T4\Orm\Model $class */
        $class = get_class($model);
        $tableName = $class::getTableName();
        /** @var \T4\Dbal\Connection $connection */
        $connection = $class::getDbConnection();

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
        return $this->deleteSubTree(get_class($model), $model->__lft, $model->__rgt);
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
        /* @var \T4\Orm\Model $class */
        $class = get_class($model);
        /* @var \T4\Dbal\Connection $connection */
        $connection = $class::getDbConnection();
        switch ($method) {
            case 'findAllParents':
                $query = new QueryBuilder;
                $query
                    ->select('*')
                    ->from($class::getTableName())
                    ->where('__lft<:lft AND __rgt>:rgt')
                    ->order('__lft')
                    ->params([':lft'=>$model->__lft, ':rgt'=>$model->__rgt]);
                return $connection->query($query->getQuery(), $query->getParams())->fetchAll(\PDO::FETCH_CLASS, $class);

            case 'findAllChildren':
                return $this->getSubTree($class, $model->__lft+1, $model->__rgt-1);
                break;

            case 'findSubTree':
                return $this->getSubTree($class, $model->__lft, $model->__rgt);
                break;

            case 'insertAfter':
                if ($argv[0] instanceof Model) {
                    $this->insertAfter(new Collection([$model]), $argv[0]);
                } else {
                    $this->insertAfter(new Collection([$model]), $class::findByPk($argv[0]));
                }
                break;
        }
        throw new Exception('Method ' . $method . ' is not found in extension ' . __CLASS__);
    }


    /**
     * Служебные методы
     */


    /**
     * Возвращает подмножество (поддерево в частном случае) дерева с заданным границами ключей (включительно!)
     * @param \T4\Orm\Model $class
     * @param int $lft
     * @param int $rgt
     * @return \T4\Core\Collection
     */
    protected function getSubTree($class, $lft, $rgt)
    {
        return $class::findAll([
            'where'=>'__lft>='.$lft.' AND __rgt<='.$rgt,
            'order'=>'__lft'
        ]);
    }

    /**
     * Удаляет поддерева начиная (и включая!) с данного элемента
     * @param \T4\Orm\Model $class
     * @param int $lft
     * @param int $rgt
     * @return boolean
     */
    protected function deleteSubTree($class, $lft, $rgt)
    {
        $tableName = $class::getTableName();
        /** @var $connection \T4\Dbal\Connection */
        $connection = $class::getDbConnection();
        $sql = "
            DELETE FROM `" . $tableName . "`
            WHERE __lft >= :lft AND __rgt <= :rgt
            ";
        $result = $connection->execute($sql, [':lft' => $lft, ':rgt' => $rgt]);
        $sql = "
            UPDATE `" . $tableName . "`
                SET __lft = IF(__lft > :lft, __lft - (:rgt - :lft + 1), __lft),
                __rgt = __rgt - (:rgt - :lft + 1)
            WHERE __rgt > :rgt
            ";
        $result = $result && $connection->execute($sql, [':lft' => $lft, ':rgt' => $rgt]);
        return $result;
    }

}