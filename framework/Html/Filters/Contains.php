<?php

namespace T4\Html\Filters;

use T4\Dbal\Connection;
use T4\Html\Filter;

class Contains
    extends Filter
{

    public function getQueryOptions($options = []) : array
    {
        if (empty($options['where'])) {
            $options['where'] = 'TRUE';
        }
        $options['where'] .= ' AND ' . $this->name . ' LIKE ' . $this->getConnection()->quote('%' . $this->value . '%') . '';
        return $options;
    }
}