<?php

namespace T4\Mvc\Extensions\Jquery;

class Extension
    extends \T4\Mvc\Extension
{

    public function init()
    {
        /** @var \T4\Mvc\AssetsManager $assets */
        $assets = $this->app->assets;
        if ( isset($this->config->location) && 'local' == $this->config->location ) {
            $assets->publishJsFile($this->assetsPath.'/lib/js/jquery-2.1.0.min.js');
        } else {
            $assets->registerJsUrl('http://code.jquery.com/jquery-2.1.0.min.js');
        }

        if (!empty($this->config->ui)) {
            $assets->registerCssUrl('//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css');
            $assets->registerJsUrl('//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js');
        }
    }

}