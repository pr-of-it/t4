<?php

namespace T4\Html\Elements;

use T4\Html\Element;

class Textarea
    extends Element
{

    /**
     * @return string
     */
    public function render()
    {
        $attrs = $this->getAttributesStr();
        $res = '<textarea' . ($attrs ? ' ' . $attrs : '') . '>' . $this->options->value . '</textarea>';
        return $res;
    }

}