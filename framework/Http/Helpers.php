<?php

namespace T4\Http;

class Helpers
{

    public static function redirect($url)
    {
        header('Location: ' . (empty($_SERVER['HTTPS']) || 'off'==$_SERVER['HTTPS'] ? 'http://' : 'https://' ).$_SERVER['HTTP_HOST'].$url, true, 302);
        exit;
    }

} 