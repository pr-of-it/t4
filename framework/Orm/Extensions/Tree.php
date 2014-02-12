<?php

namespace T4\Orm\Extensions;

use T4\Dbal\QueryBuilder;
use T4\Orm\Exception;
use T4\Orm\Extension;

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
         * Вставка новой записи в таблицу
         * родительскую запись определяем по полю __prt
         */
        if ($model->isNew()) {
            if (empty($model->__prt)) {
                $model->__prt = 0;
                $query = new QueryBuilder();
                $query->select('MAX(__rgt)')->from($tableName);
                $right = $connection->query($query->getQuery())->fetchScalar() + 1;
                $level = 0;
            } else {
                $parent = $class::findByPk($model->__prt);
                if (empty($parent))
                    return false;
                $right = $parent->__rgt;
                $level = $parent->__lvl;
            }
            $result = $connection->execute("
                UPDATE `" . $tableName . "`
                SET
                    `__rgt`=:right+2,
                    `__lft`=IF(`__lft`>:right, `__lft` + 2, `__lft`) WHERE `__rgt`>=:right
                ", [':right' => $right]);
            if (!$result) {
                return false;
            } else {
                $model->__lft = $right;
                $model->__rgt = $right + 1;
                $model->__lvl = $level + 1;
                return true;
            }
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

    public function call($model, $method, $argv)
    {
        $class = get_class($model);
        switch (true) {
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