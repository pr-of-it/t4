<?php

namespace T4\Core;

interface IArrayable
{
    public function toArray();
    public function toArrayRecursive();
    public function fromArray($data);
}