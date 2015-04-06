<?php

namespace T4\Extensions\Fileupload;

class Extension
    extends \T4\Core\Extension
{

    public function init()
    {
        $assets = $this->app->assets;
        $assets->publish($this->assetsPath . '/lib/js/');
        $assets->publishJsFile($this->assetsPath . '/lib/js/jquery.iframe-transport.js');
        $assets->publishJsFile($this->assetsPath . '/lib/js/jquery.fileupload.js');
    }

}