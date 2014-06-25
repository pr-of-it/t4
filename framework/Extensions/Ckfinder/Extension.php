<?php

namespace T4\Extensions\Ckfinder;

class Extension
    extends \T4\Core\Extension
{

    public function init()
    {

        $assets = $this->app->assets;
        $assetsUrl = $assets->publish($this->assetsPath.'/lib');

        $assets->publishJsFile($this->assetsPath.'/lib/ckfinder.js');

        $assets->registerJs(<<<FILE
$(function(){
    CKFinder.setupCKEditor(null, '{$assetsUrl}');
});
FILE
    );

    }

}