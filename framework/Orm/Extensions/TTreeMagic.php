<?php

namespace T4\Orm\Extensions;

use T4\Dbal\QueryBuilder;

trait TTreeMagic
{

    public static function hasMagicStaticMethod($method)
    {
        switch (true) {
            case 'findAllTree' == $method:
                return true;
        }
        return false;
    }

    public static function __callStatic($method, $argv)
    {
        /** @var \T4\Orm\Model $class */
        $class = $argv[0];
        array_shift($argv);
        switch (true) {
            case 'findAllTree' == $method:
                return $class::findAll(['order'=>'__lft']);
                break;
        }
    }

    public function hasMagicDynamicMethod($method)
    {
        switch ($method) {
            case 'findAllParents':
                return true;
            case 'findAllChildren':
                return true;
            case 'findSubTree':
                return true;
            case 'insertBefore':
                return true;
        }
        return false;
    }

    public function __call($method, $argv)
    {
        /** @var \T4\Orm\Model $model */
        $model = $argv[0];
        array_shift($argv);

        /* @var \T4\Orm\Model $class */
        $class = get_class($model);
        /* @var \T4\Dbal\Connection $connection */
        $connection = $class::getDbConnection();

        switch ($method) {

            case 'findAllParents':
                $query = new QueryBuilder();
                $query
                    ->select('*')
                    ->from($class::getTableName())
                    ->where('__lft<:lft AND __rgt>:rgt')
                    ->order('__lft')
                    ->params([':lft'=>$model->__lft, ':rgt'=>$model->__rgt]);
                return $class::findAllByQuery($query);

            case 'findAllChildren':
                $query = new QueryBuilder();
                $query
                    ->select('*')
                    ->from($class::getTableName())
                    ->where('__lft>:lft AND __rgt<:rgt')
                    ->order('__lft')
                    ->params([':lft'=>$model->__lft, ':rgt'=>$model->__rgt]);
                return $class::findAllByQuery($query);

            case 'findSubTree':
                $query = new QueryBuilder();
                $query
                    ->select('*')
                    ->from($class::getTableName())
                    ->where('__lft>=:lft AND __rgt<=:rgt')
                    ->order('__lft')
                    ->params([':lft'=>$model->__lft, ':rgt'=>$model->__rgt]);
                return $class::findAllByQuery($query);

            case 'insertBefore':
                $element = $argv[0];
                $this->insertModelBeforeElement($model, $element);
                return $model;
                break;

        }
    }

} 