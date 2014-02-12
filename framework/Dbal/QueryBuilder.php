<?php

namespace T4\Dbal;


class QueryBuilder
{

    protected $mode = 'select';

    protected $select;
    protected $from;
    protected $what;
    protected $where;
    protected $order;
    protected $limitFrom;
    protected $limitCount;

    protected $params = [];

    public function select($select)
    {
        $this->select = $select;
        $this->mode = 'select';
        return $this;
    }

    public function from($from)
    {
        $this->from = $from;
        return $this;
    }

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

    public function params($params)
    {
        $this->params = $params;
        return $this;
    }

    public function limit($limit)
    {
        if (!is_array($limit)) {
            $limit = preg_split('~\,[\s]*~', $limit, -1, \PREG_SPLIT_NO_EMPTY);
        }

        if (count($limit) == 1) {
            $this->limitCount = $limit[0];
        } else {
            $this->limitFrom = $limit[0];
            $this->limitCount = $limit[1];
        }

        return $this;
    }

    public function getQuery()
    {
        /*
         * SELECT statement
         */
        if ( $this->mode == 'select' )
        {
            if (empty($this->select) || empty($this->from)) {
                throw new Exception('SELECT statement must have both \'select\' and \'from\' parts');
            }

            /*
             * SELECT part
             */
            if ( $this->select == '*') {
                $sql = "SELECT *\n";
            } else {
                if (!is_array($this->select)) {
                    $this->select = preg_split('~\,[\s]*~', $this->select, -1, \PREG_SPLIT_NO_EMPTY);
                }
                // TODO: грамотное экранирование имен полей
                $sql = "SELECT `" . implode('`, `', $this->select). "`\n";
                $sql = "SELECT " . implode(', ', $this->select). "\n";
            }

            /*
             * FROM part
             */
            if (!is_array($this->from)) {
                $this->from = preg_split('~\,[\s]*~', $this->from, -1, \PREG_SPLIT_NO_EMPTY);
            }
            // TODO: грамотное экранирование имен таблиц
            $sql .= "FROM `" . implode('`, `', $this->from). "`\n";
            $sql .= "FROM " . implode(', ', $this->from). "\n";

            /*
             * WHERE part
             */
            if (!empty($this->where)) {
                $sql .= "WHERE ".$this->where."\n";
            }

            /*
             * ORDER part
             */
            if (!empty($this->order)) {
                $sql .= "ORDER BY ".$this->order."\n";
            }

            /*
             * LIMIT part
             */
            if (!empty($this->limitFrom) || !empty($this->limitCount)) {
                $sql .= "LIMIT ". (!empty($this->limitFrom) ? intval($this->limitFrom).", ".intval($this->limitCount) : intval($this->limitCount)) ."\n";
            }

            return $sql;
        }
        return '';
    }

    public function getParams()
    {
        return $this->params;
    }

    public function __toString()
    {
        return $this->getQuery();
    }

}