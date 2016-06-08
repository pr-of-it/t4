<?php

namespace T4\Dbal;

use T4\Core\Collection;

/**
 * Class Condition
 * @package T4\Dbal
 *
 * @property $where
 * @property $order
 * @property $offset
 * @property $limit
 *
 * @property array $params
 */
class Conditions
    extends Collection
{

    protected function innerSet($offset, $value)
    {
        if (is_numeric($offset) && !($value instanceof Condition)) {
            $value = new Condition($value);
        }
        parent::innerSet($offset, $value);
    }

    public function __construct($data = null, $self = false)
    {
        if (!$self) {
            $this->where = new self($data, true);
        } else {
            parent::__construct($data);
        }
    }

    public function count($self = false)
    {
        if (!$self) {
            return $this->where->count(true);
        } else {
            return parent::count();
        }
    }

    public function append($value, $self = false)
    {
        if (!($value instanceof Condition)) {
            $value = new Condition($value);
        }
        if (!$self) {
            $this->where->append($value, true);
        } else {
            parent::append($value);
        }
        return $this;
    }

    public function prepend($value, $self = false)
    {
        if (!($value instanceof Condition)) {
            $value = new Condition($value);
        }
        if (!$self) {
            $this->where->prepend($value, true);
        } else {
            parent::prepend($value);
        }
        return $this;
    }

    /*
    public function merge($values, $self = false)
    {
        foreach ($values as &$value)
        {
            if (!($value instanceof Condition)) {
                $value = new Condition($value);
            }
        }
        if (!$self) {
            $this->where->merge($values, true);
        } else {
            parent::merge($values);
        }
        return $this;
    }
    */

    public function order($val)
    {
        $this->order = $val;
        return $this;
    }

    public function offset($val)
    {
        $this->offset = $val;
        return $this;
    }

    public function limit($val)
    {
        $this->limit = $val;
        return $this;
    }

    public function params($val)
    {
        $this->params = $val;
        return $this;
    }

}