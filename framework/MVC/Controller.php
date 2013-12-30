<?php

namespace T4\MVC;

use T4\Core\Std;


abstract class Controller
{

    /**
     * Данные, которые будут переданы фронт-контроллеру для вывода в нужном формате
     * @var \T4\Core\Std
     */
    protected $data;

    /**
     * Ссылка на объект приложения
     * @var \T4\MVC\Application
     */
    public $app;

    final public function __construct()
    {
        $this->data = new Std();
        $this->app = Application::getInstance();
    }

    public function beforeAction()
    {
        return true;
    }

    public function afterAction()
    {

    }

    public function getData()
    {
        return $this->data;
    }

    /**
     * Возвращает список аргументов действия данного контроллера
     * @param $name Имя действия
     * @return array Список аргументов
     * @throws EControllerException
     */
    final public function getActionParameters($name)
    {
        $actionMethodName = 'action' . ucfirst($name);
        if (method_exists($this, $actionMethodName)) {
            $reflection = new \ReflectionMethod($this, $actionMethodName);
            $params = $reflection->getParameters();
            $ret = [];
            foreach ($params as $param) {
                $ret[] = $param->name;
            }
            return $ret;
        } else {
            throw new EControllerException('Action ' . $name . ' is not found in controller ' . get_class($this));
        }

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
            return $this->data;
        } else {
            throw new EControllerException('Action ' . $name . ' is not found in controller ' . get_class($this));
        }
    }

}