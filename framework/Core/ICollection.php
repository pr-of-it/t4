<?php

namespace T4\Core;

interface ICollection
{

    public function add($value);
    public function prepend($value);
    public function append($value);
    public function merge($values);

    public function slice($offset, $length=null);

    public function existsElement(array $attributes);
    public function findAllByAttributes(array $attributes);
    public function findByAttributes(array $attributes);

    public function asort();
    public function ksort();
    public function uasort(callable $callback);
    public function uksort(callable $callback);
    public function natsort();
    public function natcasesort();
    public function sort(callable $callback);

    public function map(callable $callback);
    public function filter(callable $callback);
    public function reduce($start, callable $callback);
    public function collect($what);
    public function group($by);

    public function __call($method, array $params = []);

}