<?php

namespace T4\Extensions\Jquery;

class Extension
    extends \T4\Core\Extension
{

    public function init()
    {
        $assets = $this->app->assets;
        if ( isset($this->options->location) && 'local'==$this->options->location ) {
            $assets->publishJs($this->path.DS.'lib'.DS.'js'.DS.'jquery-2.1.0.min.js');
        } else {
            $assets->registerJs('http://code.jquery.com/jquery-2.1.0.min.js');
        }
    }

}