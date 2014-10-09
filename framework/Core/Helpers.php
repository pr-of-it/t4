<?php

namespace T4\Core;

class Helpers
{

    public static function getRealPath($path)
    {
        if (0 === strpos($path, '///')) {
            return ROOT_PATH . DS . str_replace('/', DS, substr($path, 3));
        } elseif (0 === strpos($path, '//')) {
            return ROOT_PATH_PROTECTED . DS . str_replace('/', DS, substr($path, 2));
        } elseif (0 === strpos($path, '/')) {
            return ROOT_PATH_PUBLIC . DS . str_replace('/', DS, substr($path, 1));
        } else {
            return false;
        }
    }

    public static function canonize($some)
    {
        if (is_string($some)) {
            $some = array_map(function ($el) { return null;}, array_flip(preg_split('~[\s]*\,[\s]*~', $some, -1, \PREG_SPLIT_NO_EMPTY)));
            return $some;
        }
        if (is_array($some)) {
            $ret = [];
            foreach ($some as $key => $value) {
                if (is_numeric($key) && is_string($value)) {
                    $new = self::canonize($value);
                    $ret = array_merge($ret, $new);
                }
            }
            return $ret;
        }
        return $some;
    }

}