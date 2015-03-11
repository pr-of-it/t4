<?php

namespace T4\Core;

class Config extends Std
{

    /**
     * @param array|string|null $data
     * @throws \T4\Core\Exception
     */
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
        if (!is_readable($path)) {
            throw new Exception('Config file ' . $path . ' is not found or is not readable');
        }
        return $this->fromArray(include($path));
    }

    public function save($path)
    {
     //   $path=ROOT_PATH_PROTECTED . DS . 'config.php';
        $str=var_export($this->toArray());
        $str=str_replace( [ '(', ')' ], [ '[', ']' ], $str );
        $file = fopen($path, 'w');
        //fwrite($file,var_export($this->toArray(),true));
        fwrite($file,str,true);

        fclose($file);

    }

}