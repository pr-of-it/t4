<?php

namespace T4\Mvc\Extensions\Fileupload;

class Extension
    extends \T4\Mvc\Extension
{

    public function init()
    {
        /** @var \T4\Mvc\AssetsManager $assets */
        $assets = $this->app->assets;
        $assets->publish($this->assetsPath . '/lib/js/');
        $assets->publishJsFile($this->assetsPath . '/lib/js/jquery.iframe-transport.js');
        $assets->publishJsFile($this->assetsPath . '/lib/js/jquery.fileupload.js');
    }

}