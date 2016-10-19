<?php

namespace T4\Core;

interface IProvider
{
    public function setPageSize(int $size = 0) : IProvider;
    public function getPageSize() : int;

    public function getTotal() : int;

    public function getPages() : \Generator;

    public function getPage(int $n) : Collection;
    public function getAll() : Collection;
}