<?php

namespace T4\Core;

class Config
    extends Std
    implements IActiveRecord
{

    protected $__path;

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
        $this->__path = $path;
    }

    public function getPath()
    {
        return $this->__path;
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

    public function save()
    {
        $str = $this->prepareForSave($this->toArray());
        if (empty($this->__path)) {
            throw new Exception('Empty path for save config');
        }
        file_put_contents($this->__path, '<?php' . "\n\n" . 'return ' . $str . ';');
    }

    public function delete()
    {
        if (empty($this->__path)) {
            throw new Exception('Empty path for delete config');
        }
        if (!file_exists($this->__path) || !is_file($this->__path)) {
            throw new Exception('Config path is not file or does not exist');
        }
        unlink($this->__path);
    }
}