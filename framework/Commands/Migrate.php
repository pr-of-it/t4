<?php

namespace T4\Commands;


use T4\Console\Command;

class Migrate
    extends Command
{

    public function actionDefault() {
        echo 'Migrate up!';
    }

}