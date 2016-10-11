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

    public function __construct($data = [])
    {
        if (!empty($data)) {
            if (isset($data['select'])) {
                $this->select($data['select']);
            } elseif (isset($data['insert'])) {
                $this->insert($data['select']);
            } elseif (isset($data['update'])) {
                $this->update($data['select']);
            } elseif (isset($data['delete'])) {
                $this->delete($data['select']);
            }
            $this->table(!empty($data['table']) ? $data['table'] : '');
            $this->tables(!empty($data['tables']) ? $data['tables'] : '');
            $this->where(!empty($data['where']) ? $data['where'] : '');
            $this->from(!empty($data['from']) ? $data['from'] : '');
            $this->joins(!empty($data['joins']) ? $data['joins'] : []);
            $this->where(!empty($data['where']) ? $data['where'] : '');
            $this->group(!empty($data['group']) ? $data['group'] : '');
            $this->order(!empty($data['order']) ? $data['order'] : '');
            if (isset($data['offset'])) {
                $this->offset($data['offset']);
            }
            if (isset($data['limit'])) {
                $this->limit($data['limit']);
            }
            $this->values(!empty($data['values']) ? $data['values'] : []);
            $this->params(!empty($data['params']) ? $data['params'] : []);
        }
    }

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
        $what = array_map([$this, 'trim'], $what);
        return $what;
    }

    public function select($what = '*')
    {
        if ('*' == $what) {
            $this->select = ['*'];
        } else {
            $what = $this->prepareWhat(func_get_args());
            $this->select = array_values(array_diff(array_merge(!empty($this->select) ? $this->select : [], $what), ['*']));
        }
        $this->mode = 'select';
        return $this;
    }

    public function insert($table = null)
    {
        $this->mode = 'insert';
        if (null !== $table) {
            $this->table($table);
        }
        return $this;
    }

    public function update($table = null)
    {
        $this->mode = 'update';
        if (null !== $table) {
            $this->table($table);
        }
        return $this;
    }

    public function delete($table = null)
    {
        $this->mode = 'delete';
        if (null !== $table) {
            $this->table($table);
        }
        return $this;
    }

    public function table($table)
    {
        $what = $this->prepareWhat(func_get_args());

        switch ($this->mode) {
            case 'insert':
                $this->insertTables = array_merge(!empty($this->insertTables) ? $this->insertTables : [], $what);
                break;
            case 'update':
                $this->updateTables = array_merge(!empty($this->updateTables) ? $this->updateTables : [], $what);
                break;
            case 'delete':
                $this->deleteTables = array_merge(!empty($this->deleteTables) ? $this->deleteTables : [], $what);
                break;
            case 'select':
            default:
                $this->from($table);
                break;
        }
        return $this;
    }

    public function tables()
    {
        return call_user_func_array([$this, 'table'], func_get_args());
    }

    public function from($what)
    {
        $what = $this->prepareWhat(func_get_args());
        $this->from = array_values(array_filter(array_merge(!empty($this->from) ? $this->from : [], $what)));
        return $this;
    }

    public function join($table, $on, $type = 'full', $alias = '')
    {
        if (!isset($this->joins)) {
            $this->joins = [];
        }
        $join = [[
            'table' => $table,
            'alias' => $alias,
            'on' => $on,
            'type' => $type,
        ]];
        $this->joins = array_merge($this->joins, $join);
        return $this;
    }

    public function joins(array $joins)
    {
        $this->joins = array_merge($this->joins ?? [], $joins);
        return $this;
    }

    /**
     * Joins data from BelongsTo model relation
     *
     * @param string|\T4\Orm\Model $modelClass
     * @param string               $relationPath point-splitted relation chain
     * @return self
     * @throws \BadMethodCallException
     * @throws \InvalidArgumentException
     */
    public function with(string $modelClass, string $relationPath)
    {
        $relationNames = explode('.', $relationPath);
        foreach ($relationNames as $index => $relationName) {
            $relation = $modelClass::getRelation($relationName);
            if (empty($relation)) {
                throw new \BadMethodCallException('Relation does not exists!');
            }
            if ($relation['type'] !== \T4\Orm\Model::BELONGS_TO) {
                throw new \InvalidArgumentException('Only Belongs to relations are supported!');
            }

            /** @var \T4\Orm\Model $relationClass */
            $relationClass = $relation['model'];
            $relationParents = array_slice($relationNames, 0, $index);
            $aliasPointed = empty($relationParents) ? $relationName : implode('.',$relationParents) . '.' . $relationName;
            $relationPathUnderscored = implode('_', $relationParents);
            $aliasUnderscored = empty($relationParents) ? $relationName : $relationPathUnderscored . '_' . $relationName;

            $select = [];
            $columnNames = array_keys($relationClass::getColumns());
            foreach ($columnNames as $columnName) {
                $select["$aliasPointed.$columnName"] = "$aliasUnderscored.$columnName";
            }
            $this->select($select);

            if (empty($this->joins) || empty(array_filter($this->joins, function($join) use ($aliasUnderscored) { return $join['alias'] == $aliasUnderscored; }))) {
                $this->join($relationClass::getTableName(), $aliasUnderscored . '.' . $relationClass::PK . ' = ' . ($relationPathUnderscored ?: 't1') . '.' . $modelClass::getRelationLinkName($relation), 'left', $aliasUnderscored);
            }
            $modelClass = $relationClass;
        }
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

    public function group($group)
    {
        $this->group = $group;
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

    public function params($params)
    {
        $this->params = $params;
        return $this;
    }

    public function getQuery($driver)
    {
        if (!$driver instanceof IDriver) {
            $driver = DriverFactory::getDriver($driver);
        }
        return $driver->makeQuery($this);
    }

    public function getParams()
    {
        return $this->params;
    }

    /**
     * Merges queries
     *
     * @param array|QueryBuilder|\T4\Core\IArrayable $prototype
     * @param string $operator AND, OR
     * @return self
     * @throws \InvalidArgumentException
     * @throws \DomainException
     */
    public function merge($prototype, $operator = 'and')
    {
        if ($prototype instanceof QueryBuilder) {
            $params = $prototype->getParams();
            $prototype = $prototype->toArray();
            $prototype['params'] = $params;
        } elseif($prototype instanceof \T4\Core\IArrayable) {
            $prototype = $prototype->toArray();
        }
        if (!is_array($prototype) && !$prototype instanceof \ArrayAccess) {
            throw new \InvalidArgumentException('Invalid builder type!');
        }
        if (!empty($this->mode) && !empty($prototype['mode']) && $this->mode !== $prototype['mode']) {
            throw new \DomainException('Query mode is not much!');
        }
        if (!empty($prototype['select'])) {
            $this->select($prototype['select']);
        }
        if (!empty($prototype['from'])) {
            $this->from($prototype['from']);
        }
        if (!empty($prototype['where'])) {
            $this->where = empty($this->where) ? $prototype['where'] : "($this->where) $operator ($prototype[where])";
        }
        foreach(['group','order','limit','offset'] as $property) {
            if (!empty($prototype[$property])) {
                $this->$property = $prototype[$property];
            }
        }
        foreach(['joins','params','insertTables','updateTables','deleteTables','values'] as $arrayProperty) {
            if (!empty($prototype[$arrayProperty])) {
                $this->$arrayProperty = array_merge($this->$arrayProperty ?? [], $prototype[$arrayProperty]);
            }
        }
        return $this;
    }

}
