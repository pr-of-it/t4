<?php

namespace T4\Html;

use T4\Core\Std;

abstract class Element
{

    /**
     * @var \T4\Core\Std
     */
    protected $options;

    /**
     * @var \T4\Core\Std
     */
    protected $attributes;

    /**
     * @param string $name
     * @param array $options
     * @param array $attributes
     */
    public function __construct($name = '', $options = [], $attributes = [])
    {
        $this->options = new Std();
        foreach ($options as $name => $value)
            $this->setOption($name, $value);

        $this->attributes = new Std();
        $this->setName($name);
        foreach ($attributes as $name => $value)
            $this->setAttribute($name, $value);
    }

    /**
     * @param mixed $key
     * @param mixed $val
     * @return \T4\Html\Element $this
     */
    public function setOption($key, $val)
    {
        $this->options->$key = $val;
        return $this;
    }

    /**
     * @param string $key
     * @param string $val
     * @return \T4\Html\Element $this
     */
    public function setAttribute($key, $val)
    {
        $this->attributes->$key = $val;
        return $this;
    }

    /**
     * @param string $val
     * @return \T4\Html\Element $this
     */
    public function setName($val)
    {
        $this->attributes->name = $val;
        return $this;
    }

    /**
     * @param string $val
     * @return \T4\Html\Element $this
     */
    public function setValue($val)
    {
        $this->options->value = htmlentities($val);
        return $this;
    }

    protected function getAttributesStr()
    {
        $ret = [];
        foreach ($this->attributes as $name => $value) {
            if (!empty($value))
                $ret[] = $name . '="' . $value . '"';
        }
        return implode(" ", $ret);
    }

    /**
     * @return string
     */
    abstract public function render();

    /**
     * @return string
     */
    final public function __toString()
    {
        return $this->render();
    }

}