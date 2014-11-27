<?php

namespace T4\Dbal;

interface IDriverQueryBuilder
{
    public function makeQuery(QueryBuilder $query);
} 