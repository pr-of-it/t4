<?php

namespace T4\Mvc\Extensions\Fotorama;


class Extension
    extends \T4\Mvc\Extension
{
    public function init()
    {
        /** @var \T4\Mvc\AssetsManager $assets */
        $assets = $this->app->assets;
        $assets->publish($this->assetsPath . '/lib');
        $assets->publishCssFile($this->assetsPath . '/lib/css/fotorama.css');
        $assets->publishJsFile($this->assetsPath . '/lib/js/fotorama.js');
    }

}