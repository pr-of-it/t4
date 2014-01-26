<?php

namespace T4\Console;

use T4\Core\Config;
use T4\Core\Exception;
use T4\Core\Std;
use T4\Core\TSingleton;
use T4\Dbal\Connection;

class Application
{

    use TSingleton;

    const CMD_PATTERN = '~^(\/?)([^\/]*?)(\/([^\/]*?))?$~';
    const OPTION_PATTERN = '~^--(.+)=(.*)$~';
    const DEFAULT_ACTION = 'default';

    /**
     * @var \T4\Core\Std $db
     */
    public $db;

    private function __construct()
    {
        if ( !is_readable(ROOT_PATH_PROTECTED . DS . 'config.php') ) {
            echo 'Application is not installed. Install it using "t4 /create/app" command'."\n";
            return;
        }
        $this->config = new Config(ROOT_PATH_PROTECTED . DS . 'config.php');
        try {
            $this->db = new Std();
            foreach ($this->config->db as $connection => $connectionConfig) {
                $this->db->{$connection} = new Connection($connectionConfig);
            }
        } catch (\T4\Dbal\Exception $e) {
            echo $e->getMessage();
            die;
        }
    }


    public function run()
    {
        try {

            $route = $this->parseCmd($_SERVER['argv']);

            $commandClassName = $route['namespace'] . '\\Commands\\' . ucfirst($route['command']);
            if (!class_exists($commandClassName))
                throw new Exception('Command class ' . $commandClassName . ' is not found');
            $command = new $commandClassName;
            $actionMethodName = 'action' . ucfirst($route['action']);
            if (!method_exists($command, $actionMethodName))
                throw new Exception('Action ' . $route['action'] . ' is not found in command class ' . $commandClassName . '');

            $reflection = new \ReflectionMethod($command, $actionMethodName);
            if ($reflection->getNumberOfRequiredParameters() != count($route['params']))
                throw new Exception('Invalid required parameters count for command');
            $actionParams = $reflection->getParameters();
            $params = [];
            foreach ($actionParams as $actionParam) {
                if (!$actionParam->isOptional() && !isset($route['params'][$actionParam->name])) {
                    throw new Exception('Required parameter ' . $actionParam->name . ' is not set');
                }
                $params[$actionParam->name] = $route['params'][$actionParam->name];
            }

            $command->action($route['action'], $params);

        } catch (Exception $e) {
            die('ERROR: '. $e->getMessage());
        }
    }

    protected function parseCmd($argv)
    {

        $argv = array_slice($argv, 1);
        $cmd = array_shift($argv);
        preg_match(self::CMD_PATTERN, $cmd, $m);
        $commandName = $m[2];
        $actionName = isset($m[4]) ? $m[4] : self::DEFAULT_ACTION;
        $rootCommand = !empty($m[1]);

        $options = [];
        foreach ($argv as $arg) {
            if (preg_match(self::OPTION_PATTERN, $arg, $m)) {
                $options[$m[1]] = $m[2];
            }
        }

        return [
            'namespace' => $rootCommand ? 'T4' : 'App',
            'command' => $commandName,
            'action' => $actionName,
            'params' => $options,
        ];

    }

}