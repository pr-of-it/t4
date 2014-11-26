<?php

namespace T4\Dbal;

use T4\Core\Std;

/**
 * Class QueryBuilder
 * @package T4\Dbal
 *
 */
class QueryBuilder
    extends Std
{

    protected $leftJoin = [];
    protected $rightJoin = [];

    protected $params = [];

    protected function trim($s)
    {
        return trim($s, " \"'`\t\n\r\0\x0B");
    }

    protected function prepareWhat($what)
    {
        if (1 == count($what)) {
            if (is_array($what[0])) {
                $what = $what[0];
            } else {
                $what = preg_split('~[\s]*\,[\s]*~', $what[0]);
            }
        }
        $what = array_map([get_called_class(), 'trim'], $what);
        return $what;
    }

    public function select($what='*')
    {
        if ('*' == $what) {
            $this->select = ['*'];
        } else {
            $what = $this->prepareWhat(func_get_args());
            $this->select = array_values(array_diff( array_merge(!empty($this->select) ? $this->select : [], $what), ['*']));
        }
        $this->mode = 'select';
        return $this;
    }

    public function insert($table)
    {
        $this->insertTable = $table;
        $this->mode = 'insert';
        return $this;
    }

    public function delete($what)
    {
        $what = $this->prepareWhat(func_get_args());
        $this->deleteTables = array_merge(!empty($this->deleteTables) ? $this->deleteTables : [], $what);
        $this->mode = 'delete';
        return $this;
    }

    public function from($what)
    {
        $what = $this->prepareWhat(func_get_args());
        $this->from = array_merge(!empty($this->from) ? $this->from : [], $what);
        return $this;
    }

    /**
     * @todo: split this???
     */
    public function where($where)
    {
        $this->where = $where;
        return $this;
    }

    public function order($order)
    {
        $this->order = $order;
        return $this;
    }

    public function offset($offset)
    {
        $this->offset = (int)$offset;
        return $this;
    }

    public function limit($limit)
    {
        $this->limit = (int)$limit;
        return $this;
    }

    public function values($what)
    {
        $this->values = $what;
        return $this;
    }

    public function leftJoin($table, $on)
    {
        $join = &$this->leftJoin[];
        $join['table'] = $table;
        $join['on'] = $on;
        return $this;
    }

    public function rightJoin($table, $on)
    {
        $join = &$this->rightJoin[];
        $join['table'] = $table;
        $join['on'] = $on;
        return $this;
    }

    public function params($params)
    {
        $this->params = $params;
        return $this;
    }

    public function makeQuery($driver)
    {
        if (!$driver instanceof IDriver) {
            $driver = DriverFactory::getDriver($driver);
        }
        return $driver->makeQuery($this);

        /*
         * SELECT statement
         */
        if ( $this->mode == 'select' )
        {

            /*
             * LEFT JOIN PART
             */
            $this->leftJoin = array_map(function ($x) {
                static $i = 0;
                $i++;
                $x['table'] = $x['table'] . ' AS lj' . $i;
                return $x;
            }, $this->leftJoin);
            foreach ($this->leftJoin as $join) {
                $sql .= "LEFT JOIN " . $join['table'] . " ON " . $join['on'] . "\n";
            }

            /*
             * RIGHT JOIN PART
             */
            $this->rightJoin = array_map(function ($x) {
                static $i = 0;
                $i++;
                $x['table'] = $x['table'] . ' AS rj' . $i;
                return $x;
            }, $this->rightJoin);
            foreach ($this->rightJoin as $join) {
                $sql .= "RIGHT JOIN " . $join['table'] . " ON " . $join['on'] . "\n";
            }

            return $sql;
        }
        return '';
    }

    public function getParams()
    {
        return $this->params;
    }

}