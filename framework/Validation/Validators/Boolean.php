<?php

namespace T4\Validation\Validators;

use T4\Validation\Validator;
use T4\Validation\Exceptions\InvalidBoolean;

class Boolean extends Validator
{

    public function validate($value): bool
    {

        if ( null === filter_var($value, \FILTER_VALIDATE_BOOLEAN, \FILTER_NULL_ON_FAILURE) ) {
            throw new InvalidBoolean($value);
        }

        return true;
    }

}