<?php

namespace T4\Console;

use T4\Core\Config;
use T4\Core\Exception;
use T4\Core\ISingleton;
use T4\Core\TSingleton;
use T4\Core\TStdGetSet;
use T4\Dbal\Connections;
use T4\Threads\Helpers;


/**
 * Class Application
 * @package T4\Console
 *
 * @property string $path
 * @property \T4\Core\Config $config
 * @property \T4\Console\Request $request
 * @property \T4\Dbal\Connection[] $db
 */
class Application
    implements
        ISingleton,
        IApplication
{

    use
        TStdGetSet,
        TSingleton;

    use TRunCommand;

    const CMD_PATTERN = '~^(\/?)([^\/]*?)(\/([^\/]*?))?$~';
    const DEFAULT_COMMAND = 'Application';
    const DEFAULT_ACTION = 'Default';

    const SUCCESS_CODE = 0;
    const ERROR_CODE = 1;

    /**
     * @return string
     */
    public function getPath()
    {
        return \ROOT_PATH_PROTECTED;
    }

    /**
     * @param Config|null $config
     * @return $this
     */
    public function setConfig(Config $config = null)
    {
        $this->config = $config ?: new Config([]);
        return $this;
    }

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

        /** @var \T4\Console\Command $command */
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

    protected function getDb()
    {
        static $db = null;
        if (null === $db) {
            $db = new Connections($this->config->db);
        }
        return $db;
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