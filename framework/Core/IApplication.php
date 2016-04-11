<?php

namespace T4\Core;

interface IApplication
{

    public function getPath();
    public function setConfig(Config $config = null);

}