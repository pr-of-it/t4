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
            $assets->publishCss($this->assetsPath.'/lib/css/bootstrap.min.css');
            if ( !empty($this->options->theme) ) {
                $assets->registerCss($this->options->theme->css);
            } else {
                $assets->publishCss($this->assetsPath.'/lib/css/bootstrap-theme.min.css');
            }
            $assets->publishJs($this->assetsPath.'/lib/js/bootstrap.min.js');
        } else {
            $assets->registerCss('//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css');
            $assets->registerCss('//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap-theme.min.css');
            $assets->registerJs('//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js');
        }
    }

}