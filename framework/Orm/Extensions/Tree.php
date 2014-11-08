<?php

namespace T4\Orm\Extensions;

use T4\Dbal\QueryBuilder;
use T4\Orm\Exception;
use T4\Orm\Extension;
use T4\Orm\Model;

class Tree
    extends Extension
{

    use TTreeService, TTreeMagic;

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
     * Подготовка модели к вставке в дерево перед заданным элементом, в то же поддерево, что и заданный элемент
     * @param \T4\Orm\Model $model
     * @param \T4\Orm\Model $element
     * @throws Exception
     * @return bool
     */
    protected function insertModelBeforeElement(Model &$model, Model &$element)
    {
        if ($element->isNew())
            throw new Exception('Target model should be saved before insert');

        $this->refreshTreeColumns($element);

        /** @var \T4\Orm\Model $class */
        $class = get_class($model);
        $tableName = $class::getTableName();
        /** @var \T4\Dbal\Connection $connection */
        $connection = $class::getDbConnection();

        $this->expandTreeBeforeElement($element, $model->getTreeWidth());

        if (!$model->isNew()) {
            $this->refreshTreeColumns($model);
            $diff = $element->__lft - $model->__lft;
            $lvldiff = $element->__lvl - $model->__lvl;
            $sql = "
                UPDATE `" . $tableName . "`
                SET
                    `__lft` = `__lft` + :diff,
                    `__rgt` = `__rgt` + :diff,
                    `__lvl` = `__lvl` + :lvldiff,
                    `__prt` = IF(`__id`=:id, :parentid, `__prt`)
                WHERE `__lft` >= :lft AND `__rgt` <= :rgt
            ";
            $connection->execute($sql, [':id' => $model->getPk(), 'parentid'=>$element->__prt, ':lft' => $model->__lft, ':rgt' => $model->__rgt, ':diff' => $diff, ':lvldiff' => $lvldiff]);
            $this->removeFromTreeByElement($model);
        } else {
            // TODO: этот вариант тоже рассмотреть
            throw new Exception('Save this model before move');
        }

        // TODO: рассчитывать
        $this->refreshTreeColumns($model);
        $this->refreshTreeColumns($element);

    }

    /**
     * Подготовка модели к вставке в дерево сразу после заданного элемента, в то же поддерево, что и заданный элемент
     * @param \T4\Orm\Model $model
     * @param \T4\Orm\Model $element
     * @throws Exception
     * @return bool
     */
    protected function insertModelAfterElement(Model &$model, Model &$element)
    {
        if ($element->isNew())
            throw new Exception('Target model should be saved before insert');

        $this->refreshTreeColumns($element);

        /** @var \T4\Orm\Model $class */
        $class = get_class($model);
        $tableName = $class::getTableName();
        /** @var \T4\Dbal\Connection $connection */
        $connection = $class::getDbConnection();

        $this->expandTreeAfterElement($element, $model->getTreeWidth());

        if (!$model->isNew()) {
            $this->refreshTreeColumns($model);
            $diff = $element->__rgt - $model->__lft + 1;
            $lvldiff = $element->__lvl - $model->__lvl;
            $sql = "
                UPDATE `" . $tableName . "`
                SET
                    `__lft` = `__lft` + :diff,
                    `__rgt` = `__rgt` + :diff,
                    `__lvl` = `__lvl` + :lvldiff,
                    `__prt` = IF(`__id`=:id, :parentid, `__prt`)
                WHERE `__lft` >= :lft AND `__rgt` <= :rgt
            ";
            $connection->execute($sql, [':id' => $model->getPk(), 'parentid'=>$element->__prt, ':lft' => $model->__lft, ':rgt' => $model->__rgt, ':diff' => $diff, ':lvldiff' => $lvldiff]);
            $this->removeFromTreeByElement($model);
        } else {
            // TODO: этот вариант тоже рассмотреть
            throw new Exception('Save this model before move');
        }

        // TODO: рассчитывать
        $this->refreshTreeColumns($model);
        $this->refreshTreeColumns($element);
    }


    /**
     * Подготовка модели к вставке в дерево в качестве первого потомка заданного элемента
     * @param \T4\Orm\Model $model
     * @param \T4\Orm\Model $parent
     * @throws Exception
     */
    protected function insertModelAsFirstChildOf(Model &$model, Model &$parent)
    {
        if ($parent->isNew())
            throw new Exception('Parent model should be saved before child insert');

        /** @var \T4\Orm\Model $class */
        $class = get_class($model);
        $tableName = $class::getTableName();
        /** @var \T4\Dbal\Connection $connection */
        $connection = $class::getDbConnection();

        if (!$model->isNew()) {
            $this->refreshTreeColumns($model);
        }
        $this->refreshTreeColumns($parent);

        $width = $model->getTreeWidth();

        $sql = "
            UPDATE `" . $tableName . "`
            SET `__rgt` = `__rgt` + :width + 1
            WHERE `__lft` >= :lft
        ";
        $connection->execute($sql, [':lft' => $parent->__lft, ':width' => $width]);
        $sql = "
            UPDATE `" . $tableName . "`
            SET `__lft` = `__lft` + :width + 1
            WHERE `__lft` > :lft
        ";
        $connection->execute($sql, [':lft' => $parent->__lft, ':width' => $width]);

        $model->__lft = $parent->__lft + 1;
        $model->__rgt = $parent->__lft + $width + 1;
        $model->__lvl = $parent->__lvl + 1;
        $model->__prt = $parent->getPk();

        $parent->__rgt += $width + 1;

    }

    /**
     * Подготовка модели к вставке в дерево в качестве последнего потомка заданного элемента
     * @param \T4\Orm\Model $model
     * @param \T4\Orm\Model $parent
     * @throws Exception
     */
    protected function insertModelAsLastChildOf(Model &$model, Model &$parent)
    {
        if ($parent->isNew())
            throw new Exception('Parent model should be saved before child insert');

        /** @var \T4\Orm\Model $class */
        $class = get_class($model);
        $tableName = $class::getTableName();
        /** @var \T4\Dbal\Connection $connection */
        $connection = $class::getDbConnection();

        if (!$model->isNew()) {
            $this->refreshTreeColumns($model);
            $this->removeFromTreeByElement($model);
        }
        $this->refreshTreeColumns($parent);

        $width = $model->getTreeWidth();

        $sql = "
            UPDATE `" . $tableName . "`
            SET `__rgt` = `__rgt` + :width + 1
            WHERE `__rgt` >= :rgt
        ";
        $connection->execute($sql, [':width' => $width, ':rgt' => $parent->__rgt]);
        $sql = "
            UPDATE `" . $tableName . "`
            SET `__lft` = `__lft` + :width + 1
            WHERE `__lft` > :rgt
        ";
        $connection->execute($sql, [':width' => $width, ':rgt' => $parent->__rgt]);

        $model->__lft = $parent->__rgt;
        $model->__rgt = $parent->__rgt + $width;
        $model->__lvl = $parent->__lvl + 1;
        $model->__prt = $parent->getPk();

        $parent->__rgt += $width + 1;

    }

    /**
     * Подготовка модели к вставке в дерево в качестве первого корня
     * @param \T4\Orm\Model $model
     */
    protected function insertModelAsFirstRoot(Model &$model)
    {
        /** @var \T4\Orm\Model $class */
        $class = get_class($model);
        $tableName = $class::getTableName();
        /** @var \T4\Dbal\Connection $connection */
        $connection = $class::getDbConnection();

        $query = new QueryBuilder();
        $query->select('MIN(`__lft`)')->from($tableName);
        $minLft = (int)$connection->query($query->getQuery())->fetchScalar();

        $width = $model->getTreeWidth();
        $this->expandTreeBeforeLft($connection, $tableName, $minLft, $width);

        if (!$model->isNew()) {
            $this->refreshTreeColumns($model);
            $sql = "
                UPDATE `" . $tableName . "`
                SET
                    `__lft` = `__lft` - :lft + :min,
                    `__rgt` = `__rgt` - :lft + :min,
                    `__lvl` = `__lvl` - :lvl
                WHERE `__lft` >= :lft AND `__rgt` <= :rgt
            ";
            $connection->execute($sql, [':min' => $minLft, ':width'=> $width, ':lft' => $model->__lft, ':rgt' => $model->__rgt, ':lvl' => $model->__lvl]);
            $this->removeFromTreeByElement($model);
        }

        $model->__lft = $minLft;
        $model->__rgt = $model->__lft + $width;
        $model->__lvl = 0;
        $model->__prt = 0;
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

        $query = new QueryBuilder();
        $query->select('MAX(`__rgt`)')->from($tableName);
        $maxRgt = (int)$connection->query($query->getQuery())->fetchScalar();

        if ($model->isNew()) {

            $model->__lft = $maxRgt + 1;
            $model->__rgt = $model->__lft + 1;
            $model->__lvl = 0;
            $model->__prt = 0;

        } else {
            $sql = "
                UPDATE `" . $tableName . "`
                SET `__lft` = `__lft` + (:max - :lft + 1),
                `__rgt` = `__rgt` + (:max - :lft + 1),
                `__lvl` = `__lvl` - :lvl
                WHERE `__lft` >= :lft AND `__rgt` <= :rgt
            ";
            $connection->execute($sql, [':max' => $maxRgt, ':lft' => $model->__lft, ':rgt' => $model->__rgt, ':lvl' => $model->__lvl]);
            $this->removeFromTreeByElement($model);
            // TODO: calculate new __lft, __rgt!
            $this->refreshTreeColumns($model);
            $model->__lvl = 0;
            $model->__prt = 0;
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
        } else {
            /** @var \T4\Orm\Model $class */
            $class = get_class($model);
            $oldParent = empty($model->__prt) ? null : $class::findByPk($model->__prt);
            if ($oldParent != $model->parent) {
                $this->refreshTreeColumns($model);
                if (empty($model->parent)) {
                    $this->insertModelAsLastRoot($model);
                } else {
                    $this->insertModelAsLastChildOf($model, $model->parent);
                }
            }
        }

        return true;

    }

    /**
     * Перед удалением модели нужно обновить значения ее служебных полей из БД
     * Иначе удаление поддерева может сработать некорректно
     * @param Model $model
     * @return bool
     */
    public function beforeDelete(&$model)
    {
        $this->refreshTreeColumns($model);
        return true;
    }

    /**
     * Удаление узла дерева
     * В данном методе удаляются все его подузлы
     * @param \T4\Orm\Model $model
     * @return bool
     */
    public function afterDelete(&$model)
    {
        /** @var \T4\Orm\Model $class */
        $class = get_class($model);
        $tableName = $class::getTableName();
        /** @var \T4\Dbal\Connection $connection */
        $connection = $class::getDbConnection();

        $sql = "
            DELETE FROM `" . $tableName . "`
            WHERE `__lft` > :lft
                AND `__rgt` < :rgt
        ";
        $connection->execute($sql, [':lft' => $model->__lft, ':rgt' => $model->__rgt]);

        $this->removeFromTreeByLftRgt($connection, $tableName, $model->__lft, $model->__rgt);

        $model->__lft = 0;
        $model->__rgt = 0;
        $model->__lvl = 0;
        $model->__prt = 0;
    }

}