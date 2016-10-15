<?php

namespace T4\Html;

use T4\Core\Std;
use T4\Dbal\Query;

class Sorters
    extends Std
{

    protected static $schema = [
    ];

    public function __construct($data = null)
    {
        foreach (static::$schema['sorters'] as $key => $value) {
            $this->$key = new $value['class']($key, $data[$key] ?? null, $value['options'] ?? null);
        }
    }
    /**
     * @param \T4\Dbal\Query $query
     * @return \T4\Dbal\Query
     */
    public function modifyQuery(Query $query)
    {
        foreach ($this as $name => $sorter) {
            /** @var \T4\Html\Sorter $sorter */
            $query = $sorter->modifyQuery($query);
        }
        return $query;
    }


    /**
     * @param array $options
     * @return array
     */
    public function modifyQueryOptions($options = []) : array
    {
        foreach ($this as $name => $sorter) {
            /** @var \T4\Html\Sorter $sorter */
            $options = $sorter->modifyQueryOptions($options);
        }
        return $options;
    }

}