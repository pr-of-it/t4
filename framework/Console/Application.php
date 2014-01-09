<?php

namespace T4\Console;


use T4\Core\TSingleton;

class Application {

    use TSingleton;

    public function run()
    {
        global $argc, $argv;

        echo "Console test...\n";
        echo "argc=" . $argc . "\n";
        echo "argv=\n";
        var_dump($argv);
        echo "Echo test ... ";
        $line = trim(fgets(STDIN));
        echo $line;
    }

} 