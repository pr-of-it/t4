<?php

namespace T4\Validation;

abstract class Validator
{

    abstract public function validate($value): bool;

    final public function __invoke($value): bool
    {
        return $this->validate($value);
    }

}