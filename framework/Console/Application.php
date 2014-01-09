<?php

namespace T4\Console;


use T4\Core\TSingleton;

class Application {

    use TSingleton;

    public function run()
    {
        echo "Console test...\n";
        echo "argc=" . $_SERVER['argc'] . "\n";
        echo "argv=\n";
        var_dump($_SERVER['argv']);
        echo "Echo test ... ";
        $line = trim(fgets(STDIN));
        echo $line;
    }

} 