<?php

namespace T4\Mvc\Extensions\Ckeditor;

class Extension
    extends \T4\Mvc\Extension
{

    const EDITOR_SELECTOR = '.editor';

    public function init()
    {
        /** @var \T4\Mvc\AssetsManager $assets */
        $assets = $this->app->assets;
        $assets->publish($this->assetsPath.'/lib');

        if ( isset($this->config->location) && 'local' == $this->config->location ) {
            $assets->publishJsFile($this->assetsPath . '/lib/ckeditor.js');
            $assets->publishJsFile($this->assetsPath . '/lib/adapters/jquery.js');
        } else {
            $assets->registerJsUrl('//cdn.ckeditor.com/4.4.1/full/ckeditor.js');
        }

        $assets->publishJsFile($this->assetsPath . '/lib/t4.js');

    }

}