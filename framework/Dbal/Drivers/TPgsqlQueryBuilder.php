<?php

namespace T4\Dbal\Drivers;

use T4\Dbal\QueryBuilder;

trait TPgsqlQueryBuilder
{

    public function makeQuery(QueryBuilder $query)
    {
        switch ($query->mode) {
            case 'select':
                return $this->makeQuerySelect($query);
        }
    }

    protected function makeQuerySelect(QueryBuilder $query)
    {
        if (empty($query->select) || empty($query->from)) {
            throw new Exception('SELECT statement must have both \'select\' and \'from\' parts');
        }

        $sql  = 'SELECT ';
        if ($query->select == ['*']) {
            $sql .= '*';
        } else {
            $select = array_map(function ($x) {
                $parts = explode('.', $x);
                $lastIndex = count($parts)-1;
                foreach ($parts as $index => &$part) {
                    if (
                        $index == $lastIndex
                        ||
                        !preg_match('~^t[\d]+$~', $part)
                    ) {
                        $part = '"' . $part . '"';
                    }
                }
                return implode('.', $parts);
            }, $query->select);
            $sql .= implode(', ', $select);
        }
        $sql .= "\n";

        $sql .= 'FROM ';
        $from = array_map(function ($x) {
            static $i = 0;
            $i++;
            return '"'. $x . '" AS t' . $i;
        }, $query->from);
        $sql .= implode(', ', $from);
        $sql .= "\n";

        if (!empty($query->where)) {
            $sql .= 'WHERE ' . $query->where;
            $sql .= "\n";
        }

        if (!empty($query->order)) {
            $sql .= 'ORDER BY ' . $query->order;
            $sql .= "\n";
        }

        if (!empty($query->offset)) {
            $sql .= ' OFFSET ' . $query->offset;
            $sql .= "\n";
        }

        if (!empty($query->limit)) {
            $sql .= ' LIMIT ' . $query->limit;
            $sql .= "\n";
        }

        $sql = preg_replace('~\n$~', '', $sql);
        return $sql;
    }

} 