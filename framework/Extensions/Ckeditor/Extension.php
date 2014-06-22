<?php

namespace T4\Extensions\Ckeditor;

class Extension
    extends \T4\Core\Extension
{

    const EDITOR_SELECTOR = '.editor';

    public function init()
    {
        $assets = $this->app->assets;
        $assets->publish($this->assetsPath.'/lib');

        if ( isset($this->options->location) && 'local'==$this->options->location ) {
            $assets->publishJs($this->assetsPath.'/lib/ckeditor.js');
            $assets->publishJs($this->assetsPath.'/lib/adapters/jquery.js');
        } else {
            $assets->registerJs('//cdn.ckeditor.com/4.4.1/full/ckeditor.js');
        }

        $assets->publishJs($this->assetsPath.'/lib/t4.js');

    }

}