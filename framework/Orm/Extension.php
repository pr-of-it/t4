<?php

namespace T4\Orm;

interface Extension
{

    public function prepareColumns($columns);

    public function prepareIndexes($indexes);

} 