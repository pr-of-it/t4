<?php

namespace T4\Commands;

use T4\Console\Command;

class Application
    extends Command
{

    public function actionDefault()
    {
        $this->writeLn('T4 Console. Use it:');
        $this->writeLn('- t4 /command/action for pre-installed T4 commands');
        $this->writeLn('- t4 command/action for your application commands');
    }

}