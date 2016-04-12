<?php

namespace T4\Mvc\Extensions\Jstree;

class Extension
    extends \T4\Mvc\Extension
{

    public function init()
    {
        /** @var \T4\Mvc\AssetsManager $assets */
        $assets = $this->app->assets;
        $assets->publish($this->assetsPath . '/lib/dist/themes/default/');
        $assets->publishCssFile($this->assetsPath . '/lib/dist/themes/default/style.min.css');
        $assets->publishJsFile($this->assetsPath . '/lib/dist/jstree.min.js');
    }

}