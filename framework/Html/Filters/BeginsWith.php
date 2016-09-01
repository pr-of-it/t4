<?php

namespace T4\Html\Filters;

use T4\Dbal\Connection;
use T4\Html\Filter;

class BeginsWith
    extends Filter
{

    public function getQueryOptions(Connection $connection, $options = []) : array
    {
        if (empty($options['where'])) {
            $options['where'] = '1';
        }
        $options['where'] .= ' AND ' . $this->name . ' LIKE ' . $connection->quote($this->value . '%') . '';
        return $options;
    }
}