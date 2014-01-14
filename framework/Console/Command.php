<?php


namespace T4\Console;


class Command {

    const DEFAULT_ACTION = 'default';

    public function beforeAction()
    {
        return true;
    }

    public function afterAction()
    {

    }

    final public function action($name, array $params = [])
    {
        $actionMethodName = 'action' . ucfirst($name);
        if (method_exists($this, $actionMethodName)) {
            // Продолжаем выполнение действия только если из beforeAction не передано false
            if ($this->beforeAction()) {
                call_user_func_array([$this, $actionMethodName], $params);
                $this->afterAction();
            }
        } else {
            throw new Exception('Action ' . $name . ' is not found in command ' . get_class($this));
        }
    }

}