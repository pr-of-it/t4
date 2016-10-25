<?php

namespace T4\Core;

/**
 * Interface for objects which can be casted from array and be casted to array
 *
 * Interface IArrayable
 * @package T4\Core
 */
interface IArrayable
{
    public function fromArray($data);

    public function toArray() : array;

    public function toArrayRecursive() : array;
}