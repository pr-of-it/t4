<?php

namespace T4\Mvc\Extensions\Bootstrap;

class Extension
    extends \T4\Mvc\Extension
{

    public function init()
    {
        /** @var \T4\Mvc\AssetsManager $assets */
        $assets = $this->app->assets;
        $theme = $this->config->theme;

        $version = $this->config->version ?: '3.3.7';

        if (isset($this->config->location) && 'local' == $this->config->location) {

            $assets->publish($this->assetsPath . '/lib/' . $version);
            $assets->publishCssFile($this->assetsPath . '/lib/' . $version . '/css/bootstrap.min.css');
            if (!empty($this->config->theme)) {
                $assets->publishCssFile($this->assetsPath . '/lib/' . $version . '/css/' . $theme . '/bootstrap.min.css');
            } else {
                $assets->publishCssFile($this->assetsPath . '/lib/' . $version . '/css/bootstrap-theme.min.css');
            }
            $assets->publishJsFile($this->assetsPath . '/lib/' . $version . '/js/bootstrap.min.js');

        } else {

            $assets->registerCssUrl('https://maxcdn.bootstrapcdn.com/bootstrap/' . $version . '/css/bootstrap.min.css');
            if (!empty($this->config->theme)) {
                $assets->registerCssUrl('https://maxcdn.bootstrapcdn.com/bootswatch/' . $version . '/' . $theme . '/bootstrap.min.css');
            } else {
                $assets->registerCssUrl('https://maxcdn.bootstrapcdn.com/bootstrap/' . $version . '/css/bootstrap-theme.min.css');
            }
            $assets->registerJsUrl('https://maxcdn.bootstrapcdn.com/bootstrap/' . $version . '/js/bootstrap.min.js');

        }
    }

}