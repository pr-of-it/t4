<?php

namespace T4\Html\Filters;

use T4\Html\Filter;

class DateEquals
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
        $options['where'] .= ' AND CAST(' . $this->name . ' AS DATE) = :' . $this->name;
        $options['params'][':' . $this->name] = $this->value;
        return $options;
    }
}