<?php

namespace T4\Mvc;

use T4\Core\Config;
use T4\Core\ISingleton;
use T4\Http\Request;

interface IRouter
{

    public function setConfig(Config $config);
    public function parseRequest(Request $request) : Route;

}