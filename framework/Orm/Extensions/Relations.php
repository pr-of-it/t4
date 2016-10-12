<?php

namespace T4\Orm\Extensions;

use T4\Dbal\Query;
use T4\Orm\Extension;
use T4\Orm\Model;

class Relations
    extends Extension
{

    public function afterDelete(Model &$model)
    {
        /** @var \T4\Orm\Model $class */
        $class = get_class($model);
        $relations = $class::getRelations();
        foreach ($relations as $name => $relation) {
            switch ($relation['type']) {
                case Model::MANY_TO_MANY:
                    $linkTable = $class::getRelationLinkName($relation);
                    $query =
                        (new Query())
                        ->delete()
                        ->from($linkTable)
                        ->where($class::getManyToManyThisLinkColumnName($relation).'=:id')
                        ->params([':id' => $model->getPk()]);
                    $class::getDbConnection()->execute($query);
            }
        }
        return true;
    }

}