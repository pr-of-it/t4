<?php

namespace T4\Dbal;

/**
 * Interface IDriverQueryBuilder
 * @package T4\Dbal
 *
 * @deprecated
 */
interface IDriverQueryBuilder
{
    public function makeQuery(QueryBuilder $query);
} 