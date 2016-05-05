<?php

namespace T4\Core;

interface IProvider
{
    public function setOptions(array $options = []);
    public function setPageSize(int $size = 0);

    public function getPageSize();
    public function getTotal();

    public function getPages();
    public function getPage(int $n);
}