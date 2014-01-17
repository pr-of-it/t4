<?php


namespace T4\Commands;


use T4\Console\Command;

class Version
    extends Command
{

    public function actionDefault()
    {
        echo \T4\VERSION;
    }

} 