<?php

namespace T4\Mvc\Tags;

use T4\Mvc\Tag;

class Editor
    extends Tag
{

    public function render()
    {
        $value = $this->html;
        $class = isset($this->params->class) ? $this->params->class : '';
        unset($this->params->value);
        unset($this->params->class);
        $htmlOptions = [];
        foreach ($this->params as $name=>$val)
            $htmlOptions[] = $name . '="' . $val . '"';

        return
            '<textarea class="editor' . ($class ? ' ' . $class : '') . '"' . ($htmlOptions ? ' ' . implode(' ', $htmlOptions) : '') . '>' .
            htmlentities($value, ENT_COMPAT | ENT_HTML5, 'UTF-8', false) .
            '</textarea>';
    }

}