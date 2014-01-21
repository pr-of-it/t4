<?php


namespace T4\Commands;


use T4\Console\Command;
use T4\Fs\Helpers;

class App
    extends Command
{

    public function actionCreate()
    {
        $src = \T4\ROOT_PATH.DS.'Cabs'.DS.'Webapp';
        $dst = ROOT_PATH;
        echo 'Web-application install'."\n";

        $dst = $this->read('Root path for your application', $dst);
        echo '---> '.$dst."\n";

        $publicDirName = 'www';
        $publicDirName = $this->read('Public path for your application', $publicDirName);
        echo '---> '.$dst.DS.$publicDirName."\n";

        //Helpers::copyDir($src, $dst);

    }

}