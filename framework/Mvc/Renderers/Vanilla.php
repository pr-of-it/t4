<?php

namespace T4\Mvc\Renderers;

use T4\Core\Std;
use T4\Mvc\ARenderer;

class Vanilla
    extends ARenderer
{

    public function render($template, $data = [])
    {
        if ($data instanceof Std) {
            extract($data->getData());
        } else {
            extract((array)$data);
        }

        $templatePath = $this->findTemplate($template);
        ob_start();
        include $templatePath;
        $content = ob_get_clean();

        return $content;
    }

}