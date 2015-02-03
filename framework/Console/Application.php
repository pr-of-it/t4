<?php

namespace T4\Console;

use T4\Core\Config;
use T4\Core\Exception;
use T4\Core\Std;
use T4\Core\TSingleton;
use T4\Core\TStdGetSet;
use T4\Dbal\Connection;
use T4\Threads\Helpers;


/**
 * Class Application
 * @package T4\Console
 * @property \T4\Core\Config $config
 * @property \T4\Dbal\Connection[] $db
 */
class Application
{

    use TSingleton, TStdGetSet;

    const CMD_PATTERN = '~^(\/?)([^\/]*?)(\/([^\/]*?))?$~';
    const OPTION_PATTERN = '~^--([^=]+)={0,1}([^=]*)$~';
    const DEFAULT_ACTION = 'Default';

    const ERROR_CODE = 1;

    public function run()
    {
        try {

            $arguments = array_slice($_SERVER['argv'], 1);
            $route = $this->parseCmd($arguments);

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
            $this->shutdown('ERROR: ' . $e->getMessage());
        }
    }

    public function shutdown($message = '')
    {
        if (!empty($message)) {
            echo $message . "\n";
        }
        exit(self::ERROR_CODE);
    }

    /**
     * @param callable $callback
     * @param array $args
     * @throws \T4\Threads\Exception
     * @return int Child process PID
     */
    public function runLater(callable $callback, $args=[])
    {
        return Helpers::run($callback, $args);
    }


    protected function parseCmd($argv)
    {
        if (empty($argv)) {
            $emptyCommand = true;
        } else {
            $emptyCommand = false;
        }
        $cmd = array_shift($argv);
        preg_match(self::CMD_PATTERN, $cmd, $m);
        $commandName = ucfirst($m[2]);

        $actionName = isset($m[4]) ? $m[4] : self::DEFAULT_ACTION;

        $rootCommand = !empty($m[1]) || $emptyCommand;

        $options = [];
        foreach ($argv as $arg) {
            if (preg_match(self::OPTION_PATTERN, $arg, $m)) {
                $options[$m[1]] = $m[2] ?: true;
            }
        }

        return [
            'namespace' => $rootCommand ? 'T4' : 'App',
            'command' => $commandName ? $commandName : 'Application',
            'action' => ucfirst($actionName),
            'params' => $options,
        ];
    }

    /**
     * Lazy Getters
     */

    public function getConfig()
    {
        try {
            return new Config(ROOT_PATH_PROTECTED . DS . 'config.php');
        } catch (Exception $e) {
            $this->shutdown('BOOTSTRAP ERROR: ' . $e->getMessage());
        }
    }

    public function getDb()
    {
        try {
            $db = new Std();
            foreach ($this->config->db as $connection => $connectionConfig) {
                $db->{$connection} = new Connection($connectionConfig);
            }
            return $db;
        } catch (Exception $e) {
            $this->shutdown('BOOTSTRAP ERROR: ' . $e->getMessage());
        }
    }

}