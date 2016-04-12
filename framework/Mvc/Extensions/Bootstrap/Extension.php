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

        if (isset($this->config->location) && 'local' == $this->config->location) {

            $assets->publish($this->assetsPath . '/lib');

            $assets->publishCssFile($this->assetsPath . '/lib/css/bootstrap.min.css');

            if (!empty($this->config->theme)) {
                $assets->publishCssFile($this->assetsPath . '/lib/css/' . $theme . '/bootstrap.min.css');
            } else {
                $assets->publishCssFile($this->assetsPath . '/lib/css/bootstrap-theme.min.css');
            }

            $assets->publishJsFile($this->assetsPath . '/lib/js/bootstrap.min.js');

        } else {

            $assets->registerCssUrl('//netdna.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css');
            if (!empty($this->config->theme)) {
                $assets->registerCssUrl('//maxcdn.bootstrapcdn.com/bootswatch/3.3.2/' . $theme . '/bootstrap.min.css');
            } else {
                $assets->registerCssUrl('//netdna.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap-theme.min.css');
            }
            $assets->registerJsUrl('//netdna.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js');

        }
    }

}