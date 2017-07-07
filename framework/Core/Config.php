<?php

namespace T4\Core;

use T4\Fs\Helpers;

/**
 * Class Config
 * @package T4\Core
 */
class Config
    extends Std
    implements IActiveRecord
{

    protected $__path;

    /**
     * @param array|string|null $arg
     * @throws \T4\Core\Exception
     * @property $path string
     */
    public function __construct($arg = null)
    {
        if (is_string($arg)) {
            $this->load($arg);
        } else {
            parent::__construct($arg);
        }
    }

    /**
     * @param string $path
     * @return $this
     * @throws \T4\Core\Exception
     */
    public function load(string $path)
    {
        if (!is_readable($path)) {
            throw new Exception('Config file ' . $path . ' is not found or is not readable');
        }
        $this->setPath($path);
        return $this->fromArray(include($path));
    }


    /**
     * @param string $path
     * @return $this
     */
    public function setPath(string $path)
    {
        $this->__path = $path;
        return $this;
    }

    /**
     * @return string
     */
    public function getPath() : string
    {
        return $this->__path;
    }

    /**
     * Saves config file
     * @return $this
     * @throws \T4\Core\Exception
     */
    public function save()
    {
        $str = $this->prepareForSave($this->toArray());
        if (empty($this->__path)) {
            throw new Exception('Empty path for config save');
        }
        file_put_contents($this->__path, '<?php' . PHP_EOL . PHP_EOL . 'return ' . $str . ';');
        return $this;
    }

    /**
     * Deletes config file
     * @return bool
     * @throws \T4\Core\Exception|\T4\Fs\Exception
     */
    public function delete()
    {
        if (empty($this->__path)) {
            throw new Exception('Empty path for config delete');
        }
        return Helpers::removeFile($this->__path);
    }

    /**
     * Prepares array representation for save in PHP file
     * @param array $data
     * @return string
     */
    protected function prepareForSave(array $data) : string
    {
        $str = var_export($data, true);
        $str = preg_replace(['~^(\s*)array\s*\($~im', '~^(\s*)\)(\,?)$~im', '~\s+$~im'], ['$1[', '$1]$2', ''], $str);
        return $str;
    }

    protected function innerSet($key, $val)
    {
        if ('path' === $key) {
            $this->__data['path'] = $val;
        } else {
            parent::innerSet($key, $val);
        }
    }

    protected function innerGet($key)
    {
        if ('path' === $key) {
            return $this->__data['path'];
        } else {
            return parent::innerGet($key);
        }
    }

}