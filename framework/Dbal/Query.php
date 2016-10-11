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
 * @property array $joins
 *
 * @property string $where
 * @property array $group
 * @property string $having
 * @property array $order
 * @property int $offset
 * @property int $limit
 *
 * @property array $values
 * @property array $params
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
        $trimmed = trim($s, " \"'`\t\n\r\0\x0B");
        if (
            false !== strpos($trimmed, ' ')
            ||
            false !== stripos($trimmed, 'as')
            ||
            false !== stripos($trimmed, 'asc')
            ||
            false !== stripos($trimmed, 'desc')
        ) {
            return $s;
        }
        return $trimmed;
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

    /**
     * Set all tables and insert mode on
     * @param mixed $tables
     * @return $this
     */
    public function insert($tables = null)
    {
        if (null !== $tables) {
            $this->tables($tables);
        }
        $this->mode = 'insert';
        return $this;
    }

    /**
     * Set all tables and update mode on
     * @param mixed $tables
     * @return $this
     */
    public function update($tables = null)
    {
        if (null !== $tables) {
            $this->tables($tables);
        }
        $this->mode = 'update';
        return $this;
    }

    /**
     * Set all tables and de;ete mode on
     * @param mixed $tables
     * @return $this
     */
    public function delete($tables = null)
    {
        if (null !== $tables) {
            $this->tables($tables);
        }
        $this->mode = 'delete';
        return $this;
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
     * Add one join statement to query
     * @param string $table
     * @param string $on
     * @param string $type
     * @param string $alias
     * @return $this
     */
    public function join($table, $on, $type = 'full', $alias = '')
    {
        if (!isset($this->joins)) {
            $this->joins = [];
        }
        $join = [
            'table' => $this->trimName($table),
            'on' => $on,
            'type' => $type,
        ];
        if (!empty($alias)) {
            $join['alias'] = $this->trimName($alias);
        }
        $this->joins = array_merge($this->joins, [$join]);
        return $this;
    }

    /**
     * Set all query's joins
     * @param array $joins
     * @return $this
     */
    public function joins($joins)
    {
        $this->joins = [];
        foreach ($joins as $join) {
            $j = [
                'table' => $this->trimName($join['table']),
                'on' => $join['on'],
                'type' => $join['type'],
            ];
            if (!empty($alias)) {
                $j['alias'] = $this->trimName($join['alias']);
            }
            $this->joins = array_merge($this->joins, [$join]);
        }
        return $this;
    }

    /**
     * Sets WHERE condition
     * @param string $where
     * @return $this
     */
    public function where($where)
    {
        $this->where = $where;
        return $this;
    }

    /**
     * Sets group values
     * @param string $group
     * @return $this
     */
    public function group($group)
    {
        $group = $this->prepareNames(func_get_args());
        $this->group = $group;
        return $this;
    }

    /**
     * Sets HAVING condition
     * @param string $having
     * @return $this
     */
    public function having($having)
    {
        $this->having = $having;
        return $this;
    }

    /**
     * Sets order directions
     * @param string $order
     * @return $this
     */
    public function order($order)
    {
        $order = $this->prepareNames(func_get_args());
        $this->order = $order;
        return $this;
    }

    /**
     * Sets offset
     * @param int $offset
     * @return $this
     */
    public function offset(int $offset)
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * Sets limit
     * @param int $limit
     * @return $this
     */
    public function limit(int $limit)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Add one query's value for insert
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function value($key, $value)
    {
        $this->values = array_merge($this->values ?? [], [$this->trimName($key) => $value]);
        return $this;
    }

    /**
     * Sets all query's values for insert
     * @param array $values
     * @return $this
     */
    public function values(array $values = [])
    {
        $values = array_combine(array_map([$this, 'trimName'], array_keys($values)), array_values($values));
        $this->values = $values;
        return $this;
    }

    /**
     * Sets one bind parameter
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function param($key, $value)
    {
        $this->params = array_merge($this->params ?? [], [$key => $value]);
        return $this;
    }

    /**
     * Sets all query's bind parameters
     * @param array $values
     * @return $this
     */
    public function params(array $values = [])
    {
        $this->params = $values;
        return $this;
    }

}