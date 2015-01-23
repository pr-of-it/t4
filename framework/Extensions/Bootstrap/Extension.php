<?php

namespace T4\Extensions\Bootstrap;

class Extension
    extends \T4\Core\Extension
{

    public function init()
    {
        $assets = $this->app->assets;
        if ( isset($this->options->location) && 'local'==$this->options->location ) {
            $assets->publish($this->assetsPath.'/lib');
            $assets->publishCssFile($this->assetsPath.'/lib/css/bootstrap.min.css');
            if ( !empty($this->options->theme) ) {
                if (!empty($this->options->theme->css)) {
                    $assets->registerCssUrl($this->options->theme->css);
                }
            } else {
                $assets->publishCssFile($this->assetsPath.'/lib/css/bootstrap-theme.min.css');
            }
            $assets->publishJsFile($this->assetsPath.'/lib/js/bootstrap.min.js');
        } else {
            $assets->registerCssUrl('//netdna.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css');
            $assets->registerCssUrl('//netdna.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap-theme.min.css');
            $assets->registerJsUrl('//netdna.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js');
        }
    }

}