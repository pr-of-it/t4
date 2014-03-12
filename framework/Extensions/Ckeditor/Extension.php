<?php

namespace T4\Extensions\Ckeditor;

class Extension
    extends \T4\Core\Extension
{

    const EDITOR_SELECTOR = '.editor';

    public function init()
    {
        $assets = $this->app->assets;
        $assets->publish($this->path.DS.'lib');
    }

}