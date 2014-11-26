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
            case 'insert':
                return $this->makeQueryInsert($query);
            case 'delete':
                return $this->makeQueryDelete($query);
        }
    }

    protected function quoteName($name)
    {
        $parts = explode('.', $name);
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
    }

    protected function aliasTableName($name, $type='main', $counter)
    {
        $typeAliases = ['main' => 't'];
        return $this->quoteName($name) . ' AS ' . $typeAliases[$type] . $counter;
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
            $select = array_map([get_called_class(), 'quoteName'], $query->select);
            $sql .= implode(', ', $select);
        }
        $sql .= "\n";

        $sql .= 'FROM ';
        $driver = $this;
        $from = array_map(function ($x) use ($driver) {
            static $c = 1;
            return $this->aliasTableName($x, 'main', $c++);
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

    protected function makeQueryInsert(QueryBuilder $query)
    {
        if (empty($query->insertTable) || empty($query->values)) {
            throw new Exception('INSERT statement must have both \'insert table\' and \'values\' parts');
        }

        $sql  = 'INSERT INTO ';
        $sql .= $this->quoteName( $query->insertTable );
        $sql .= "\n";

        $sql .= '(';
        $sql .= implode(', ', array_map([get_called_class(), 'quoteName'], array_keys($query->values)));
        $sql .= ')';
        $sql .= "\n";

        $sql .= 'VALUES (';
        $sql .= implode(', ', $query->values);
        $sql .= ')';
        $sql .= "\n";

        $sql = preg_replace('~\n$~', '', $sql);
        return $sql;
    }

}