<?php

namespace T4\Dbal;


interface IDriver
{

    public function findAllByColumn($class, $column, $value);

    public function findByColumn($class, $column, $value);

    public function save($model);

    public function delete($model);

} 