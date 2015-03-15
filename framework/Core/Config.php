<?php

namespace T4\Core;

class Config extends Std
{

    protected $path;

    /**
     * @param array|string|null $data
     * @throws \T4\Core\Exception
     * @property $path string
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
        $this->setPath($path);
        return $this->fromArray(include($path));
    }

    public function setPath($path)
    {
        $this->path = $path;
    }

    public function save()
    {
        $str = $this->prepareForSave($this->toArray());
        file_put_contents($this->path, '<?php' . "\n\n" . 'return ' . $str . ';');
    }


    /**
     * Prepares array representation for save in PHP file
     * @param array $data
     * @return string
     */
    protected function prepareForSave(array $data)
    {
        $str = var_export($data, true);
        $str = preg_replace(['~^(\s*)array\s*\($~im', '~^(\s*)\)(\,?)$~im', '~\s+$~im'], ['$1[', '$1]$2', ''], $str);
        return $str;
    }

}