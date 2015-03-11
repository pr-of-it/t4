<?php

namespace T4\Core;

class Config extends Std
{

    /**
     * @param array|string|null $data
     * @throws \T4\Core\Exception
     * @property $path string
     */
    public $path;
    public function __construct($data = null)
    {
        if (null !== $data) {
            if (is_array($data)) {
                parent::__construct($data);
            } else {
                $this->load((string)$data);
            }
        }
    }

    /**
     * @param string $path
     * @return \T4\Core\Config
     * @throws \T4\Core\Exception
     */
    public function load($path)
    {
        $this->path=$path;
        if (!is_readable($path)) {
            throw new Exception('Config file ' . $path . ' is not found or is not readable');
        }
        return $this->fromArray(include($path));
    }


    public function save()
    {
        $str = var_export($this->toArray(), true);
        $str = str_replace(['array', '(', ')'], [' ', '[', ']'], $str);
        file_put_contents($this->path,'<?php' . "\r\n" . "\r\n" . 'return '.$str.';');
    }

}