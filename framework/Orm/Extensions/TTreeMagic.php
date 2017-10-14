<?php

namespace T4\Orm\Extensions;

use T4\Dbal\Query;

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
            case 'refreshTreeColumns':
            case 'getTreeWidth':
            case 'findAllParents':
            case 'findAllChildren':
            case 'hasChildren':
            case 'findSubTree':
            case 'hasPrevSibling':
            case 'getPrevSibling':
            case 'hasNextSibling':
            case 'getNextSibling':
            case 'insertBefore':
            case 'insertAfter':
            case 'moveToFirstPosition':
            case 'moveToLastPosition':
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
        $tableName = $class::getTableName();
        /** @var \T4\Dbal\Connection $connection */
        $connection = $class::getDbConnection();

        switch ($method) {

            case 'refreshTreeColumns':
                $sql = (new Query())
                    ->select(['__lft', '__rgt', '__lvl', '__prt'])
                    ->from($tableName)
                    ->where($class::PK . '=:id')
                    ->params([':id' => $model->getPk()]);
                $columns = $connection->query($sql)->fetch(\PDO::FETCH_ASSOC);
                $model->merge($columns);
                return $model;

            case 'getTreeWidth':
                if ($model->isNew())
                    return 1;
                else
                    return $model->__rgt - $model->__lft;

            case 'findAllParents':
                $query = new Query();
                $query
                    ->select('*')
                    ->from($class::getTableName())
                    ->where('__lft<:lft AND __rgt>:rgt')
                    ->order('__lft')
                    ->params([':lft'=>$model->__lft, ':rgt'=>$model->__rgt]);
                return $class::findAllByQuery($query);

            case 'hasChildren':
                return $model->__rgt - $model->__lft > 1;
                /*
                $query = new Query();
                $query
                    ->select('COUNT(*)')
                    ->from($class::getTableName())
                    ->where('__lft>:lft AND __rgt<:rgt')
                    ->order('__lft')
                    ->params([':lft'=>$model->__lft, ':rgt'=>$model->__rgt]);
                return 0 != $connection->query($query)->fetchScalar();
                */

            case 'findAllChildren':
                $query = new Query();
                $query
                    ->select('*')
                    ->from($class::getTableName())
                    ->where('__lft>:lft AND __rgt<:rgt')
                    ->order('__lft')
                    ->params([':lft'=>$model->__lft, ':rgt'=>$model->__rgt]);
                return $class::findAllByQuery($query);

            case 'findSubTree':
                $query = new Query();
                $query
                    ->select('*')
                    ->from($class::getTableName())
                    ->where('__lft>=:lft AND __rgt<=:rgt')
                    ->order('__lft')
                    ->params([':lft'=>$model->__lft, ':rgt'=>$model->__rgt]);
                return $class::findAllByQuery($query);

            case 'hasPrevSibling':
                $query = new Query();
                $query
                    ->select('COUNT(*)')
                    ->from($class::getTableName())
                    ->where('__rgt<:lft AND __prt=:prt')
                    ->params([':lft'=>$model->__lft, ':prt'=>$model->__prt]);
                return 0 != $connection->query($query)->fetchScalar();

            case 'getPrevSibling':
                $query = new Query();
                $query
                    ->select('*')
                    ->from($class::getTableName())
                    ->where('__rgt<:lft AND __prt=:prt')
                    ->order('__lft DESC')
                    ->limit(1)
                    ->params([':lft'=>$model->__lft, ':prt'=>$model->__prt]);
                return $class::findByQuery($query);

            case 'hasNextSibling':
                $query = new Query();
                $query
                    ->select('COUNT(*)')
                    ->from($class::getTableName())
                    ->where('__lft>:rgt AND __prt=:prt')
                    ->params([':rgt'=>$model->__rgt, ':prt'=>$model->__prt]);
                return 0 != $connection->query($query)->fetchScalar();

            case 'getNextSibling':
                $query = new Query();
                $query
                    ->select('*')
                    ->from($class::getTableName())
                    ->where('__lft>:rgt AND __prt=:prt')
                    ->order('__lft')
                    ->limit(1)
                    ->params([':rgt'=>$model->__rgt, ':prt'=>$model->__prt]);
                return $class::findByQuery($query);

            case 'insertBefore':
                $element = $argv[0];
                $this->insertModelBeforeElement($model, $element);
                return $model;
                break;

            case 'insertAfter':
                $element = $argv[0];
                $this->insertModelAfterElement($model, $element);
                return $model;
                break;

            case 'moveToFirstPosition':
                $parent = $model->parent;
                if (empty($parent)) {
                    $this->insertModelAsFirstRoot($model);
                } else {
                    $this->insertModelAsFirstChildOf($model, $parent);
                }
                return $model;

            case 'moveToLastPosition':
                $parent = $model->parent;
                if (empty($parent)) {
                    $this->insertModelAsLastRoot($model);
                } else {
                    $this->insertModelAsLastChildOf($model, $parent);
                }
                return $model;

        }
    }

} 