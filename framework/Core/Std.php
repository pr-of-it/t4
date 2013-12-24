<?php

namespace T4\Core;

class Std implements \ArrayAccess, \Countable {

    private $data = [];

    public function __construct() {
        set_error_handler([$this, 'errorHandler'], E_WARNING);
    }

    public function isEmpty() {
        return 0 === count($this->data);
    }

    public function errorHandler($errno, $errstr, $errfile, $errline, $errcontext) {
        if ( 'Creating default object from empty value' == $errstr ) {
            /*
            if ( preg_match("/Indirect modification of overloaded property ([a-zA-Z0-9\\\\_]+)\:\:[\$]([\S]+) has no effect/i", $errcontext['php_errormsg'], $m) ) {
                $this->{$m[2]} = new static;
                return true;
            }
            */
            return true;
        }
        return false;
    }

    /**
     * Object access implementation
     */

    public function __get($prop) {
        return $this->data[$prop];
    }

    public function __set($prop, $value) {
        $this->data[$prop] = $value;
    }

    public function __isset($prop) {
        return isset($this->data[$prop]);
    }

    public function __unset($prop) {
        unset($this->data[$prop]);
    }

    /**
     * ArrayAccess implementation
     */

    public function offsetExists($offset) {
        return isset($this->data[$offset]);
    }

    public function offsetGet($offset) {
        return $this->data[$offset];
    }

    public function offsetSet($offset, $value) {
        $this->data[$offset] = $value;
    }

    public function offsetUnset($offset) {
        unset($this->data[$offset]);
    }

    /**
     * Countable implementation
     */

    public function count() {
        return count($this->data);
    }

}