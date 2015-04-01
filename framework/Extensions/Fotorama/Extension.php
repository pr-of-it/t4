<?php

namespace T4\Extensions\Fotorama;


class Extension
    extends \T4\Core\Extension
{
    public function init()
    {
        $assets = $this->app->assets;
        $assets->publish($this->assetsPath.'/lib');
        $assets->publishCssFile($this->assetsPath.'/lib/css/fotorama.css');
        $assets->publishJsFile($this->assetsPath.'/lib/js/fotorama.js');
    }

}