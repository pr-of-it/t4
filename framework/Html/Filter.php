<?php

namespace T4\Html;

use T4\Dbal\Connection;

abstract class Filter
{

    protected $name;
    protected $value;
    protected $options = [];

    public function __construct($name, $value, $options = [])
    {
        $this->name  = $name;
        $this->value = $value;
        if (!empty($options)) {
            $this->setOptions($options);
        }
    }

    protected function setOptions($options = [])
    {
        $this->options = $options;
        return $this;
    }

    abstract public function getQueryOptions(Connection $connection, $options = []) : array;

    //abstract public function renderFormElement(array $htmlOptions = []) : string;

}