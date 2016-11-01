<?php

namespace T4\Validation\Validators;

use T4\Validation\Validator;
use T4\Validation\Exceptions\InvalidInt;
use T4\Validation\Exceptions\OutOfRange;

class IntNum
    extends Validator
{

    protected $min;
    protected $max;

    public function __construct($min = null, $max = null)
    {
        $this->min = $min;
        $this->max = $max;
    }

    public function validate($value): bool
    {
        if ( false === filter_var($value, \FILTER_VALIDATE_INT) ) {
            throw new InvalidInt($value);
        }

        if (isset($this->min) || isset($this->max)) {
            $options = [];
            if (isset($this->min)) {
                $options['options']['min_range'] = $this->min;
            }
            if (isset($this->max)) {
                $options['options']['max_range'] = $this->max;
            }
            if ( false === filter_var($value, \FILTER_VALIDATE_INT, $options) ) {
                throw new OutOfRange($value);
            }
        }
        return true;
    }
}