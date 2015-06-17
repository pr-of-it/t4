<?php

namespace T4\Core;

class Flash
    extends Std
{

    const FLASH_KEY = '__flash';

    public function __construct($data = null)
    {
        if (null != $data) {
            parent::__construct($data);
        }
        $savedData = unserialize(Session::get(self::FLASH_KEY));
        if (!empty($savedData)) {
            $this->merge($savedData);
        }
    }

    public function __set($key, $val)
    {
        parent::__set($key, $val);
        Session::set(self::FLASH_KEY, serialize($this->getData()));
    }

    public function __get($key)
    {
        if (!isset($this->{$key}))
            return null;
        $val = parent::__get($key);
        unset($this->{$key});
        Session::set(self::FLASH_KEY, serialize($this->getData()));
        return $val;
    }

    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }


}