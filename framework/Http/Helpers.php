<?php

namespace T4\Http;

class Helpers
{

    public static function redirect($url)
    {
        header('Location: ' . $url, true, 303);
        exit;
    }

} 