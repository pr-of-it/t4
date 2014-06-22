<?php

namespace T4\Extensions\Ckfinder;

class Extension
    extends \T4\Core\Extension
{

    public function init()
    {

        $assets = $this->app->assets;
        $assets->publish($this->assetsPath.'/lib');

        $assets->publishJs($this->assetsPath.'/lib/ckfinder.js');
        $assets->publishJs($this->assetsPath.'/lib/t4.js');

    }

}