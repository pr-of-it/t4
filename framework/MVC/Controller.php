<?php

namespace T4\MVC;

use T4\Core\Std;


class Controller
{

    /**
     * Данные, которые будут переданы фронт-контроллеру для вывода в нужном формате
     * @var \T4\Core\Std
     */
    public $data;
    /**
     * Ссылка на объект приложения
     * @var \T4\MVC\Application
     */
    protected $app;

    final public function __construct()
    {
        $this->data = new Std();
        $this->app = Application::getInstance();
    }

    public function beforeAction()
    {

    }

    public function afterAction()
    {

    }

    final public function getActionParameters()
    {
        $actionMethodName = 'action' . $name;
        if (method_exists($this, $actionMethodName)) {
            $reflection = new \ReflectionFunction([$this, $actionMethodName]);
            $params = $reflection->getParameters();
            // TODO тут надо доделать
        } else {
            throw new EControllerException('Action ' . $name . ' is not found in controller ' . get_class());
        }

    }

    final public function action($name, array $params = [])
    {
        $actionMethodName = 'action' . $name;
        if (method_exists($this, $actionMethodName)) {
            $this->beforeAction();
            call_user_func_array([$this, $actionMethodName], $params);
            $this->afterAction();
            return $this->data;
        } else {
            throw new EControllerException('Action ' . $name . ' is not found in controller ' . get_class());
        }
    }

}