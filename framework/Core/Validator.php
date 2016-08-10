<?php

namespace T4\Core;

abstract class Validator
{

    abstract public function validate($value): bool;

    public function __invoke($value)
    {
        return $this->validate($value);
    }

}