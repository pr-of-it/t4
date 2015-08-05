<?php

namespace T4\Dbal\Drivers;

use T4\Dbal\Exception;
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
            case 'update':
                return $this->makeQueryUpdate($query);
            case 'delete':
                return $this->makeQueryDelete($query);
        }
    }

    protected function aliasTableName($name, $type='main', $counter)
    {
        $typeAliases = ['main' => 't', 'join' => 'j'];
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

        if (!empty($query->joins)) {
            $driver = $this;
            $joins = array_map(function ($x) use ($driver) {
                static $c = 1;
                $table =  $this->aliasTableName($x['table'], 'join', $c++);
                $x['table'] = $table;
                return $x;
            }, $query->joins);
            $j = [];
            foreach ($joins as $join) {
                switch ($join['type']) {
                    case 'full':
                        $ret = 'FULL JOIN';
                        break;
                    case 'left':
                        $ret = 'LEFT JOIN';
                        break;
                    case 'right':
                        $ret = 'RIGHT JOIN';
                        break;
                }
                $j[] = $ret . ' ' . $join['table'] . ' ON ' . $join['on'];
            };
            $sql .= implode("\n", $j);
            $sql .= "\n";
        }

        if (!empty($query->where)) {
            $sql .= 'WHERE ' . $query->where;
            $sql .= "\n";
        }

        if (!empty($query->group)) {
            $sql .= 'GROUP BY ' . $query->group;
            $sql .= "\n";
        }

        if (!empty($query->order)) {
            $sql .= 'ORDER BY ' . $query->order;
            $sql .= "\n";
        }

        if (!empty($query->offset)) {
            $sql .= 'OFFSET ' . $query->offset;
            $sql .= "\n";
        }

        if (!empty($query->limit)) {
            $sql .= 'LIMIT ' . $query->limit;
            $sql .= "\n";
        }

        $sql = preg_replace('~\n$~', '', $sql);
        return $sql;
    }

    protected function makeQueryInsert(QueryBuilder $query)
    {
        if (empty($query->insertTables) || empty($query->values)) {
            throw new Exception('INSERT statement must have both \'insert tables\' and \'values\' parts');
        }

        $sql  = 'INSERT INTO ';
        $driver = $this;
        $tables = array_map(function ($x) use ($driver) {
            return $driver->quoteName($x);
        }, $query->insertTables);
        $sql .= implode(', ', $tables);
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

    protected function makeQueryUpdate(QueryBuilder $query)
    {
        if (empty($query->updateTables) || empty($query->values)) {
            throw new Exception('UPDATE statement must have both \'update tables\' and \'values\' parts');
        }

        $sql  = 'UPDATE ';
        $driver = $this;
        $tables = array_map(function ($x) use ($driver) {
            return $driver->quoteName($x);
        }, $query->updateTables);
        $sql .= implode(', ', $tables);
        $sql .= "\n";

        $sets = [];
        foreach ($query->values as $key => $value) {
            $sets[] = static::quoteName($key) . '=' . $value;
        }

        $sql .= 'SET ' . implode(', ', $sets);
        $sql .= "\n";

        if (!empty($query->where)) {
            $sql .= 'WHERE ' . $query->where;
            $sql .= "\n";
        }

        $sql = preg_replace('~\n$~', '', $sql);
        return $sql;
    }

    protected function makeQueryDelete(QueryBuilder $query)
    {
        if (empty($query->deleteTables)) {
            throw new Exception('DELETE statement must have \'delete tables\' part');
        }

        if (empty($query->deleteTables)) {
            throw new Exception('DELETE statement must have \'delete tables\' part');
        }

        $sql  = 'DELETE FROM ';
        $driver = $this;
        $tables = array_map(function ($x) use ($driver) {
            static $c = 1;
            return $this->aliasTableName($x, 'main', $c++);
        }, $query->deleteTables);
        $sql .= implode(', ', $tables);
        $sql .= "\n";

        if (!empty($query->where)) {
            $sql .= 'WHERE ' . $query->where;
            $sql .= "\n";
        }

        $sql = preg_replace('~\n$~', '', $sql);
        return $sql;
    }

}