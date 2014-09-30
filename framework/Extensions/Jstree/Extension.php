<?php

namespace T4\Extensions\Jstree;

class Extension
    extends \T4\Core\Extension
{

    public function init()
    {
        $assets = $this->app->assets;
        $assets->publish($this->assetsPath.'/lib/dist/themes/default/');
        $assets->publishCssFile($this->assetsPath.'/lib/dist/themes/default/style.min.css');
        $assets->publishJsFile($this->assetsPath.'/lib/dist/jstree.min.js');
    }

}