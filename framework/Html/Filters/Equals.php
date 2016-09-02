<?php

namespace T4\Html\Filters;

use T4\Dbal\Connection;
use T4\Html\Filter;

class Equals
    extends Filter
{

    public function getQueryOptions($options = []) : array
    {
        if (empty($options['where'])) {
            $options['where'] = 'TRUE';
        }
        $options['where'] .= ' AND ' . $this->name . ' = :' . $this->name;
        $options['params'][':' . $this->name] = $this->value;
        return $options;
    }
}