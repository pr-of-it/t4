<?php

namespace T4\Orm\Extensions;

use T4\Dbal\QueryBuilder;
use T4\Orm\Extension;
use T4\Orm\Model;

class Relations
    extends Extension
{

    public function afterDelete(Model &$model)
    {
        $class = get_class($model);
        $relations = $class::getRelations();
        foreach ($relations as $name => $relation) {
            switch ($relation['type']) {
                case Model::MANY_TO_MANY:
                    $linkTable = $class::getRelationLinkName($relation);
                    $query = new QueryBuilder();
                    $query
                        ->delete()
                        ->table($linkTable)
                        ->where($class::getManyToManyThisLinkColumnName().'=:id');
                    $query->params([':id'=>$model->getPk()]);
                    $class::getDbConnection()->execute($query);
            }
        }
        return true;
    }

}