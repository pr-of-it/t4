<?php

namespace T4\Validation\Validators;

use T4\Validation\Validator;
use T4\Validation\Exceptions\EmptyValue;
use T4\Validation\Exceptions\InvalidIpV4;

class IpV4 extends Validator
{

    public function validate($value): bool
    {
        if (empty($value)) {
            throw new EmptyValue($value);
        }

        if ( false === filter_var($value, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV4) ) {
            throw new InvalidIpV4($value);
        }

        return true;
    }

}