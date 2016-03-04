<?php

namespace T4\Extensions\Ckfinder;

class Extension
    extends \T4\Core\Extension
{

    public function init()
    {

        $assets = $this->app->assets;
        $assetsUrl = $assets->publish($this->assetsPath.'/lib');

        $fileName = ROOT_PATH_PUBLIC . $assetsUrl . '/config.php';
        file_put_contents($fileName, str_replace(['{{ROOT_PATH_PROTECTED}}', '{{ROOT_PATH_T4}}'], [ROOT_PATH_PROTECTED, \T4\ROOT_PATH], file_get_contents($fileName)));

        $assets->publishJsFile($this->assetsPath.'/lib/ckfinder.js');

        $assets->registerJs(<<<FILE
$(function(){
    CKFinder.setupCKEditor(null, '{$assetsUrl}');
});
FILE
    );

    }

}