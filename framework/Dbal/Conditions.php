<?php

namespace T4\Dbal;

use T4\Core\Collection;

/**
 * Class Condition
 * @package T4\Dbal
 *
 * @property \T4\Core\Collection $where
 * @property $order
 * @property $offset
 * @property $limit
 *
 * @property array $params
 */
class Conditions
    extends Collection
{

    public function __construct($data = null)
    {
        $this->where = new Collection();
        if (null !== $data) {
            foreach ($data as $offset => &$value) {
                if (!($value instanceof Condition)) {
                    $value = new Condition($value);
                }
                $this->where[$offset] = $value;
            }
        }
    }

    public function count()
    {
        return $this->where->count();
    }

    public function append($value)
    {
        if (!($value instanceof Condition)) {
            $value = new Condition($value);
        }
        $this->where->append($value);
        return $this;
    }

    public function prepend($value, $self = false)
    {
        if (!($value instanceof Condition)) {
            $value = new Condition($value);
        }
        $this->where->prepend($value);
        return $this;
    }

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