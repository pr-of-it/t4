<?php

namespace T4\Console;

class Command
{

    const DEFAULT_ACTION = 'default';

    /**
     * Ссылка на объект приложения
     * @var \T4\Console\Application
     */
    public $app;

    final public function __construct()
    {
        $this->app = \T4\Console\Application::instance();
    }


    protected function beforeAction()
    {
        return true;
    }

    protected function afterAction()
    {

    }

    final public function action($name, $params = [])
    {
        $name = ucfirst($name);
        $actionMethodName = 'action' . $name;

        if (!method_exists($this, $actionMethodName)) {
            throw new Exception('Action ' . $name . ' is not found in command ' . get_class($this));
        }

        if ($this->beforeAction()) {

            $p = [];
            foreach ($this->getActionParameters($name) as $param) {

                if (!empty($params[$param->name])) {
                    $p[$param->name] = $params[$param->name];
                    unset($params[$param->name]);
                } elseif ($param->isDefaultValueAvailable()) {
                    $p[$param->name] = $param->getDefaultValue();
                } else {
                    throw new Exception('Missing argument ' . $param->name . ' for action ' . $actionMethodName);
                }

            }
            $p = array_merge($p, (array)$params);

            call_user_func_array([$this, $actionMethodName], $p);
            $this->afterAction();
        }
    }

    /**
     * @param string $name
     * @return \ReflectionParameter[]
     * @throws Exception
     */
    final protected function getActionParameters($name)
    {
        $actionMethodName = 'action' . ucfirst($name);
        if (method_exists($this, $actionMethodName)) {
            $reflection = new \ReflectionMethod($this, $actionMethodName);
            return $reflection->getParameters();
        } else {
            throw new Exception('Action ' . $name . ' is not found in command ' . get_class($this));
        }

    }

    protected function writeLn($msg)
    {
        echo $msg . "\n";
    }

    protected function read($message, $default = '', $echo = true)
    {
        echo $message . (!empty($default) ? ' [' . $default . ']' : '') . ': ';
        $line = fgets(STDIN);
        $line = str_replace(["\n", "\r"], '', $line);

        if (empty($line))
            $val = $default;
        else
            $val = $line;

        if ($echo) {
            $this->writeLn('---> ' . $val);
        }

        return $val;

    }

}