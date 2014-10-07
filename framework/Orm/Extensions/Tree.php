<?php

namespace T4\Orm\Extensions;

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
     * "Обновление" служебных полей модели из хранилища
     * @param Model $model
     */
    protected function refreshTreeColumns(Model &$model)
    {
        /** @var \T4\Orm\Model $class */
        $class = get_class($model);
        $tableName = $class::getTableName();
        /** @var \T4\Dbal\Connection $connection */
        $connection = $class::getDbConnection();

        $sql = new QueryBuilder();
        $sql->select(['__lft', '__rgt', '__lvl', '__prt'])
            ->from($tableName)
            ->where('`' . $class::PK . '`=:id');
        $columns = $connection->query($sql->getQuery(), [':id' => $model->getPk()])->fetch();
        $model->merge($columns);
    }

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
     * Создает пустое место в дереве заданной ширины
     * Место создается ПЕРЕД элементом с заданным lft
     * @param Connection $connection
     * @param string $table
     * @param int $lft
     * @param int $width
     */
    protected function expandTree(Connection $connection, $table, $lft, $width)
    {
        $sql = "
                UPDATE `" . $table . "`
                SET
                    `__lft` = IF(`__lft` >= :lft, `__lft` + (:width + 1), `__lft`),
                    `__rgt` = `__rgt` + (:width + 1)
                WHERE `__rgt` > :lft
            ";
        $connection->execute($sql, [':lft' => $lft, ':width' => $width]);
    }

    /**
     * Подготовка модели к вставке в дерево перед заданным элементом, в то же поддерево, что и заданный элемент
     * @param \T4\Orm\Model $model
     * @param \T4\Orm\Model $element
     * @throws \T4\Orm\Exception
     * @return bool
     */
    protected function insertModelBeforeElement(Model &$model, Model &$element)
    {
        if ($element->isNew())
            throw new \T4\Orm\Exception('Target model should be saved before insert');

        /** @var \T4\Orm\Model $class */
        $class = get_class($model);
        $tableName = $class::getTableName();
        /** @var \T4\Dbal\Connection $connection */
        $connection = $class::getDbConnection();

        if ($model->isNew()) {
            $width = 1;
        } else {
            $width = $model->__rgt - $model->__lft;
        }

        $this->expandTree($connection, $tableName, $element->__lft, $width);

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
            $this->removeFromTree($connection, $tableName, $model->__lft, $model->__rgt);
        }

        $model->__lft = $element->__lft;
        $model->__rgt = $element->__lft + $width + 1;
        $model->__lvl = $element->__lvl;
        $model->__prt = $element->__prt;

        $this->refreshTreeColumns($element);

    }

    /**
     * Подготовка модели к вставке в дерево сразу после заданного элемента, в то же поддерево, что и заданный элемент
     * @param \T4\Orm\Model $model
     * @param \T4\Orm\Model $element
     * @throws \T4\Orm\Exception
     * @return bool
     */
    protected function insertModelAfterElement(Model &$model, Model &$element)
    {
        if ($element->isNew())
            throw new \T4\Orm\Exception('Target model should be saved before insert');

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

        if (!$model->isNew()) {
            $this->removeFromTree($connection, $tableName, $model->__lft, $model->__rgt);
            $modelWidth = $model->__rgt - $model->__lft;
        } else {
            $modelWidth = 1;
        }

        $this->refreshTreeColumns($parent);

        $sql = "
            UPDATE `" . $tableName . "`
            SET `__rgt` = `__rgt` + :width + 1
            WHERE `__rgt` >= :rgt
        ";
        $connection->execute($sql, [':width' => $modelWidth, ':rgt' => $parent->__rgt]);
        $sql = "
            UPDATE `" . $tableName . "`
            SET `__lft` = `__lft` + :width + 1
            WHERE `__lft` > :rgt
        ";
        $connection->execute($sql, [':width' => $modelWidth, ':rgt' => $parent->__rgt]);

        $model->__lft = $parent->__rgt;
        $model->__rgt = $parent->__rgt + $modelWidth;
        $model->__lvl = $parent->__lvl + 1;
        $model->__prt = $parent->getPk();

        $parent->__rgt += $modelWidth + 1;

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
            $width = $model->__rgt - $model->__lft;
            $sql = "
                UPDATE `" . $tableName . "`
                SET `__lft` = `__lft` + (:max - :lft + 1),
                `__rgt` = `__rgt` + (:max - :lft + 1),
                `__lvl` = `__lvl` - :lvl
                WHERE `__lft` >= :lft AND `__rgt` <= :rgt
            ";
            $connection->execute($sql, [':max' => $maxRgt, ':lft' => $model->__lft, ':rgt' => $model->__rgt, ':lvl' => $model->__lvl]);
            $this->removeFromTree($connection, $tableName, $model->__lft, $model->__rgt);
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

        $this->removeFromTree($connection, $tableName, $model->__lft, $model->__rgt);

        $model->__lft = 0;
        $model->__rgt = 0;
        $model->__lvl = 0;
        $model->__prt = 0;
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
                $query = new QueryBuilder();
                $query
                    ->select('*')
                    ->from($class::getTableName())
                    ->where('__lft>:lft AND __rgt<:rgt')
                    ->order('__lft')
                    ->params([':lft'=>$model->__lft, ':rgt'=>$model->__rgt]);
                return $connection->query($query->getQuery(), $query->getParams())->fetchAll(\PDO::FETCH_CLASS, $class);

            case 'findSubTree':
                $query = new QueryBuilder();
                $query
                    ->select('*')
                    ->from($class::getTableName())
                    ->where('__lft>=:lft AND __rgt<=:rgt')
                    ->order('__lft')
                    ->params([':lft'=>$model->__lft, ':rgt'=>$model->__rgt]);
                return $connection->query($query->getQuery(), $query->getParams())->fetchAll(\PDO::FETCH_CLASS, $class);

            case 'insertBefore':
                $element = $argv[0];
                $this->insertModelBeforeElement($model, $element);
                break;

        }

        throw new Exception('Method ' . $method . ' is not found in extension ' . __CLASS__);

    }

}