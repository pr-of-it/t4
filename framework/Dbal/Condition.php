<?php

namespace T4\Dbal;

class Condition
{

    const UNARY_OPERATORS = ['NOT'];
    const BINARY_OPERATORS = ['=', '<>', '!=', '<', '>', '<=', '>=', 'IN', 'LIKE'];

    protected $x;
    protected $operator;
    protected $y;

    protected $params = [];

    public function __construct($data)
    {
        if (is_array($data) || $data instanceof \ArrayAccess) {
            $this->x = $data['x'] ?? null;
            $this->operator = $data['operator'] ?? null;
            $this->y = $data['y'] ?? null;
            $this->params = $data['params'] ?? [];
        } else {
            $this->fromString((string)$data);
        }
    }

    public function fromString($str)
    {
        foreach (self::UNARY_OPERATORS as $operator) {
            if (preg_match('~^' . preg_quote($operator, '~') . '\s*(?P<x>\S+)$~i', trim($str), $m)) {
                $this->operator = $operator;
                $this->x = $m['x'];
                return $this;
            }
        }
        foreach (self::BINARY_OPERATORS as $operator) {
            if ( preg_match('~^(?P<x>\S+)\s*' . preg_quote($operator, '~') . '\s*(?P<y>\S+)$~i', trim($str), $m) ) {
                $this->x = $m['x'];
                $this->operator = $operator;
                $this->y = $m['y'];
                return $this;
            }
        }
    }

    public function toString()
    {
        switch ( true ) {
            case in_array($this->operator, self::UNARY_OPERATORS):
                return $this->operator . ' ' . $this->x;
                break;
            case in_array($this->operator, self::BINARY_OPERATORS):
                return $this->x . ' ' . $this->operator . ' ' . $this->y;
                break;
        }
    }

    public function __toString()
    {
        return $this->toString();
    }
    
    public function setParams(array $params = [])
    {
        $this->params = $params;
        return $this;
    }

}