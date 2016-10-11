<?php

namespace T4\Dbal;

use T4\Core\Std;

/**
 * Class Query
 * @package T4\Dbal
 *
 * @property string $mode
 *
 * @property array $columns
 * @property array $tables
 */
class Query
    extends Std
{

    /**
     * @param string $s
     * @return string
     */
    protected function trimName($s)
    {
        return trim($s, " \"'`\t\n\r\0\x0B");
    }

    /**
     * @param $names
     * @return array
     */
    protected function prepareNames($names = [])
    {
        if (1 == count($names)) {
            if (is_array($names[0])) {
                $names = $names[0];
            } else {
                $names = preg_split('~[\s]*\,[\s]*~', $names[0]);
            }
        }
        $names = array_map([$this, 'trimName'], $names);
        return $names;
    }

    /**
     * Add one table to query
     * @param mixed $table
     * @return $this
     */
    public function table($table = [])
    {
        $tables = $this->prepareNames(func_get_args());
        $this->tables = array_merge($this->tables ?: [], $tables);
        return $this;
    }

    /**
     * Set all query's tables
     * @param mixed $table
     * @return $this
     */
    public function tables($table = [])
    {
        $tables = $this->prepareNames(func_get_args());
        $this->tables = $tables;
        return $this;
    }

    /**
     * Add one column name to query
     * @param mixed $column
     * @return $this
     */
    public function column($column = '*')
    {
        if ('*' == $column) {
            $this->columns = ['*'];
        } else {
            $columns = $this->prepareNames(func_get_args());
            $this->columns = array_merge(
                empty($this->columns) || ['*'] == $this->columns ? [] : $this->columns,
                array_values(array_diff($columns, ['*']))
            );
        }
        return $this;
    }

    /**
     * Set all query's columns
     * @param mixed $columns
     * @return $this
     */
    public function columns($columns = '*')
    {
        if ('*' == $columns) {
            $this->columns = ['*'];
        } else {
            $columns = $this->prepareNames(func_get_args());
            $this->columns = array_values(array_diff($columns, ['*']));
        }
        return $this;
    }

    /**
     * Set all columns and select mode on
     * @param mixed $columns
     * @return $this
     */
    public function select($columns = '*')
    {
        $this->columns(...func_get_args());
        $this->mode = 'select';
        return $this;
    }

    /**
     * Set all tables and select mode on
     * @param mixed $table
     * @return $this
     */
    public function from($table = [])
    {
        $this->tables(...func_get_args());
        $this->mode = 'select';
        return $this;
    }

}