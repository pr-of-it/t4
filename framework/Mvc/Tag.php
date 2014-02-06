<?php

namespace T4\Mvc;

use T4\Core\Exception;
use T4\Core\Std;

abstract class Tag {

    const PARAM_PAIR_STR_TEMPLATE = '~([a-z][a-z0-9_-]*)=\"(.*)\"~i';

    protected $name;
    protected $params;

    final public function __construct($params='')
    {
        $classNameParts = explode('\\', get_class($this));
        $this->name = strtolower(str_replace('Tag', '', array_pop($classNameParts)));
        $this->params = $this->parseParams($params);
    }

    final protected function parseParams($str)
    {
        $paramPairs = preg_split('~[\s]+~', $str, -1, PREG_SPLIT_NO_EMPTY);
        if ( empty($paramPairs) )
            return [];
        $ret = new Std();
        foreach ($paramPairs as $pair) {
            if (!preg_match(self::PARAM_PAIR_STR_TEMPLATE, $pair, $m)) {
                throw new Exception('Invalid tag params format: `' . $pair . '`');
            }
            $ret->{$m[1]} = $m[2];
        }
        return $ret;
    }

    final public function __toString()
    {
        return $this->render();
    }

    abstract protected function render();

}