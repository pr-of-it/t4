<?php

namespace T4\Core;

interface ISingleton
{

    public static function instance($new = false);

}