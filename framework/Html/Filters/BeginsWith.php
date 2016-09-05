<?php

namespace T4\Html\Filters;

use T4\Html\Filter;

class BeginsWith
    extends Filter
{

    public function getQueryOptions($options = []) : array
    {
        if ('' === $this->value || null === $this->value) {
            return $options;
        }
        if (empty($options['where'])) {
            $options['where'] = 'TRUE';
        }
        $options['where'] .= ' AND ' . $this->name . ' LIKE ' . $this->getConnection()->quote($this->value . '%') . '';
        return $options;
    }
}