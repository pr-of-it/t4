<?php

namespace T4\Extensions\Bootstrap;

class Extension
    extends \T4\Core\Extension
{

    public function init()
    {
        $assets = $this->app->assets;
        $assets->publishCss($this->path.DS.'lib'.DS.'css'.DS.'bootstrap.min.css');
        $assets->publishCss($this->path.DS.'lib'.DS.'css'.DS.'bootstrap-theme.min.css');
        $assets->publishJs($this->path.DS.'lib'.DS.'js'.DS.'bootstrap.min.js');
    }

}