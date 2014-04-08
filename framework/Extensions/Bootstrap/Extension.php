<?php

namespace T4\Extensions\Bootstrap;

class Extension
    extends \T4\Core\Extension
{

    public function init()
    {
        $assets = $this->app->assets;
        if ( isset($this->options->location) && 'local'==$this->options->location ) {
            $assets->publishCss($this->path.DS.'lib'.DS.'css'.DS.'bootstrap.min.css');
            $assets->publishCss($this->path.DS.'lib'.DS.'css'.DS.'bootstrap-theme.min.css');
            $assets->publishJs($this->path.DS.'lib'.DS.'js'.DS.'bootstrap.min.js');
        } else {
            $assets->registerCss('//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css');
            $assets->registerCss('//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap-theme.min.css');
            $assets->registerJs('//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js');
        }
    }

}