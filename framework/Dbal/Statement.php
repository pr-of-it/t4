<?php

namespace T4\Dbal;

/**
 * Class Statement
 * @package T4\Dbal
 */
class Statement
    extends \PDOStatement
{

    public function fetchScalar()
    {
        return $this->fetchColumn(0);
    }

    public function fetchAllObjects($class)
    {
        return $this->fetchAll(\PDO::FETCH_CLASS, $class);
    }

}