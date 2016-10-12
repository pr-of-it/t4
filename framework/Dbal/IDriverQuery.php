<?php

namespace T4\Dbal;

interface IDriverQuery
{
    public function makeQueryString(Query $query) : string;
} 