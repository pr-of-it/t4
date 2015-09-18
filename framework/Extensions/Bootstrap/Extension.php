<?php

namespace T4\Extensions\Bootstrap;

class Extension
    extends \T4\Core\Extension
{

    public function init()
    {
        $assets = $this->app->assets;
        $theme = $this->options->theme;
        if (isset($this->options->location) && 'local' == $this->options->location) {

            $assets->publish($this->assetsPath . '/lib');

            $assets->publishCssFile($this->assetsPath.'/lib/css/bootstrap.min.css');

            if (!empty($this->options->theme)) {
                $assets->publishCssFile($this->assetsPath . '/lib/css/' . $theme . '/bootstrap.min.css');
            } else {
                $assets->publishCssFile($this->assetsPath . '/lib/css/bootstrap-theme.min.css');
            }

            $assets->publishJsFile($this->assetsPath . '/lib/js/bootstrap.min.js');

        } else {

            $assets->registerCssUrl('//netdna.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css');
            if (!empty($this->options->theme)) {
                $assets->registerCssUrl('//maxcdn.bootstrapcdn.com/bootswatch/3.3.5/' . $theme . '/bootstrap.min.css');
            } else {
                $assets->registerCssUrl('//netdna.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css');
            }
            $assets->registerJsUrl('//netdna.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js');

        }
    }

}