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
 * @property \T4\Console\Request $request
 * @property \T4\Dbal\Connection[] $db
 */
class Application
{

    use TSingleton, TStdGetSet;

    const CMD_PATTERN = '~^(\/?)([^\/]*?)(\/([^\/]*?))?$~';
    const DEFAULT_COMMAND = 'Application';
    const DEFAULT_ACTION = 'Default';

    const SUCCESS_CODE = 0;
    const ERROR_CODE = 1;

    public function run()
    {
        try {

            $this->runRequest($this->request);

        } catch (Exception $e) {
            $this->halt('ERROR: ' . $e->getMessage());
        }
    }

    protected function runRequest(Request $request)
    {
        $route = $this->parseRequest($request);

        $commandClassName = $route['namespace'] . '\\Commands\\' . ucfirst($route['command']);
        if (!class_exists($commandClassName))
            throw new Exception('Command class ' . $commandClassName . ' is not found');

        $command = new $commandClassName;
        $command->action($route['action'], $route['options']);
    }

    protected function parseRequest(Request $request)
    {
        if (empty($request->command)) {
            $emptyCommand = true;
        } else {
            $emptyCommand = false;
        }
        preg_match(self::CMD_PATTERN, $request->command, $m);
        $commandName = ucfirst($m[2]);
        $actionName = isset($m[4]) ? $m[4] : self::DEFAULT_ACTION;
        $rootCommand = !empty($m[1]) || $emptyCommand;

        return [
            'namespace' => $rootCommand ? 'T4' : 'App',
            'command' => $commandName ? $commandName : self::DEFAULT_COMMAND,
            'action' => ucfirst($actionName),
            'options' => $request->options,
        ];
    }

    public function end($message = '')
    {
        if (!empty($message)) {
            echo $message . "\n";
        }
        exit(self::SUCCESS_CODE);
    }

    public function halt($message = '')
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
    public function runLater(callable $callback, $args = [])
    {
        return Helpers::run($callback, $args);
    }

    /**
     * Lazy Getters
     */

    public function getConfig()
    {
        try {
            return new Config(ROOT_PATH_PROTECTED . DS . 'config.php');
        } catch (Exception $e) {
            $this->halt('BOOTSTRAP ERROR: ' . $e->getMessage());
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
            $this->halt('BOOTSTRAP ERROR: ' . $e->getMessage());
        }
    }

    public function getRequest()
    {
        static $request = null;
        if (null === $request) {
            $request = new Request();
        }
        return $request;
    }

}