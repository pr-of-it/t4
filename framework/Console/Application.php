<?php

namespace T4\Console;


use T4\Core\Exception;
use T4\Core\TSingleton;

class Application
{

    use TSingleton;

    const CMD_PATTERN = '~^(\/?)([^\/]*?)(\/([^\/]*?))?$~';
    const DEFAULT_ACTION = 'default';

    public function run()
    {

        try {

            $route = $this->parseCmd($_SERVER['argv']);
            var_dump($route);
            die;

        } catch (Exception $e) {
            die($e->getMessage());
        }

        echo "Console test...\n";
        echo "argc=" . $_SERVER['argc'] . "\n";
        echo "argv=\n";
        var_dump($_SERVER['argv']);
        echo "Echo test ... ";
        $line = trim(fgets(STDIN));
        echo $line;
    }

    protected function parseCmd($argv)
    {

        $argv = array_slice($argv, 1);
        $cmd = array_shift($argv);
        preg_match(self::CMD_PATTERN, $cmd, $m);
        $commandName = $m[2];
        $actionName = isset($m[4]) ? $m[4] : self::DEFAULT_ACTION;
        $rootCommand = !empty($m[1]);

        return [
            'namespace' => $rootCommand ? 'T4' : 'App',
            'command'   => $commandName,
            'action'    => $actionName,
            'params'    => $argv,
        ];

    }

}