<?php

namespace T4\Extensions\Ckfinder;

class Extension
    extends \T4\Core\Extension
{

    public function init()
    {

        $assets = $this->app->assets;
        $assetsUrl = $assets->publish($this->assetsPath.'/lib');

        // TODO: отвратительное решение!
        $content = <<<FILE
$(function(){
    CKFinder.setupCKEditor(null, '{$assetsUrl}');
});
FILE;
        file_put_contents($this->path.DS.'lib'.DS.'t4.js', $content );

        $assets->publishJs($this->assetsPath.'/lib/ckfinder.js');
        $assets->publishJs($this->assetsPath.'/lib/t4.js');

    }

}