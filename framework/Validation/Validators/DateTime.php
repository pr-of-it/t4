<?php

namespace T4\Validation\Validators;

use T4\Validation\Validator;
use T4\Validation\Exceptions\EmptyValue;
use T4\Validation\Exceptions\InvalidDateTime;

class DateTime extends Validator
{

    public function validate($value): bool
    {
        if (empty($value)) {
            throw new EmptyValue($value);
        }

        if ($value instanceof \DateTimeInterface) {
            return true;
        }

        if ( false === strtotime($value) ) {
            throw new InvalidDateTime($value);

        };

        return true;
    }

}