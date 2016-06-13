<?php

namespace T4\Crypt;

class Helpers
{

    public static function hashPassword($password)
    {
        $salt = '$2a$8$' . substr(str_replace('+', '.', base64_encode(pack('N4', mt_rand(), mt_rand(), mt_rand(), mt_rand()))), 0, 22) . '$';
        return crypt($password, $salt);
    }

    public static function checkPassword($password, $hash)
    {
        return crypt($password, $hash) == $hash;
    }

}