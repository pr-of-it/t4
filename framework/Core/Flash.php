<?php

namespace T4\Core;

class Flash
    extends Std
{

    const FLASH_KEY = '__flash';

    public function __set($key, $val)
    {
        Session::set(self::FLASH_KEY.':'.$key, $val);
        $this->{$key} = $val;
    }

    public function __get($key)
    {
        if (!isset($this->{$key}))
            $this->{$key} = Session::get(self::FLASH_KEY.':'.$key);
        $val = $this->{$key};
        unset($this->{$key});
        Session::clear(self::FLASH_KEY.':'.$key);
        return $val;
    }

}