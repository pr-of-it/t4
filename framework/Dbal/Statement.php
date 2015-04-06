<?php

namespace T4\Dbal;


class Statement
    extends \PDOStatement
{

    public function fetchScalar()
    {
        return $this->fetchColumn(0);
    }

}