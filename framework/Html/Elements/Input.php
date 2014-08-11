<?php

namespace T4\Html\Elements;

use T4\Html\Element;

class Input
    extends Element
{

    public function setType($type)
    {
        $this->attributes->type = $type;
    }

    /**
     * @return string
     */
    public function render()
    {
        if (isset($this->options->value))
            $this->attributes->value = $this->options->value;
        $attrs = $this->getAttributesStr();
        $res = '<input' . ($attrs ? ' ' . $attrs : '') . ' />';
        return $res;
    }

}