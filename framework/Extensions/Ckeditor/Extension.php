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
        $assets->publishJs($this->path.DS.'lib'.DS.'ckeditor.js');
        $assets->publishJs($this->path.DS.'lib'.DS.'adapters'.DS.'jquery.js');
        $assets->publishJs($this->path.DS.'lib'.DS.'t4.js');
    }

}