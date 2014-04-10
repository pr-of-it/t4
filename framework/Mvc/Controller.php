<?php

namespace T4\Mvc;

use T4\Core\Std;
use T4\Http\Helpers;

abstract class Controller
{

    const ERROR_NO_ACCESS = 1;

    /**
     * Правила контроля доступа
     * @var array
     */
    protected $access = [];

    /**
     * Данные, которые будут переданы фронт-контроллеру для вывода в нужном формате
     * @var \T4\Core\Std
     */
    protected $data;

    /**
     * Ссылка на объект приложения
     * @var \T4\Mvc\Application
     */
    public $app;

    /**
     * Ссылка на настроенный на данный контроллер объект View
     * @var \T4\Mvc\View
     */
    public $view;

    final public function __construct()
    {
        $this->data = new Std();
        $this->app = Application::getInstance();

        $templatesPaths = [];
        if ('' == $this->getModuleName()) {
            $templatesPaths[] = $this->app->getPath() . DS . 'Templates' . DS . $this->getShortName();
        } else {
            $templatesPaths[] = $this->app->getPath() . DS . 'Modules' . DS . $this->getModuleName() . DS . 'Templates' . DS . $this->getShortName();
        }
        if ('' != $this->getModuleName() && is_readable($moduleLayoutPath = $this->app->getPath() . DS . 'Layouts' . DS . $this->getModuleName())) {
            $templatesPaths[] = $moduleLayoutPath;
        }
        $templatesPaths[] = $this->app->getPath() . DS . 'Layouts';

        $this->view = new View($templatesPaths);
        $this->view->setController($this);
    }

    public function getModuleName()
    {
        static $moduleName = null;
        if (is_null($moduleName)) {
            if (preg_match('~App\\\\Modules\\\\(.*?)\\\\~', get_class($this), $m)) {
                $moduleName = $m[1];
            } else
                $moduleName = '';
        }
        return $moduleName;
    }

    public function getShortName()
    {
        $classNameParts = explode('\\', get_class($this));
        return array_pop($classNameParts);
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
     * @throws ControllerException
     */
    final public function getActionParameters($name)
    {
        $actionMethodName = 'action' . ucfirst($name);
        if (method_exists($this, $actionMethodName)) {
            $reflection = new \ReflectionMethod($this, $actionMethodName);
            return $params = $reflection->getParameters();
        } else {
            throw new ControllerException('Action ' . $name . ' is not found in controller ' . get_class($this));
        }

    }

    final public function action($name, $params = [])
    {
        $actionMethodName = 'action' . ucfirst($name);
        if (!method_exists($this, $actionMethodName)) {
            throw new ControllerException('Action ' . $name . ' is not found in controller ' . get_class($this));
        }

        // Проверяем правила контроля доступа к заданному action
        if (!empty($this->access) && isset($this->access[$name])) {
            // @ - доступ только зарегистрированным пользователям
            if ('@'==$this->access[$name] && empty($this->app->user)) {
                throw new ControllerException('User is not logged in. Access denied.', self::ERROR_NO_ACCESS);
            }
            // массив - описывает подробные правила доступа для зарегистрированных пользователей
            if (is_array($this->access[$name])) {
                if (empty($this->app->user)) {
                    throw new ControllerException('User is not logged in. Access denied.', self::ERROR_NO_ACCESS);
                }
                foreach ($this->access[$name] as $col=>$val ) {
                    if ($this->app->user->{$col} != $val) {
                        throw new ControllerException('User ' . $col . ' is invalid. Access denied.', self::ERROR_NO_ACCESS);
                    }
                }
            }
        }

        // Продолжаем выполнение действия только если из beforeAction не передано false
        if ($this->beforeAction()) {

            $p = [];
            foreach ($this->getActionParameters($name) as $param) {

                if (!empty($params[$param->name])) {
                    $p[$param->name] = $params[$param->name];
                    unset($params[$param->name]);
                } elseif (!empty($_POST[$param->name])) {
                    $p[$param->name] = $_POST[$param->name];
                } elseif (!empty($_GET[$param->name])) {
                    $p[$param->name] = $_GET[$param->name];
                } elseif ( $param->isDefaultValueAvailable() ) {
                    $p[$param->name] = $param->getDefaultValue();
                } else {
                    throw new ControllerException('Missing argument ' . $param->name . ' for action ' . $actionMethodName);
                }

            }
            $p = array_merge($p, (array)$params);

            call_user_func_array([$this, $actionMethodName], $p);
            $this->afterAction();
        }

        return $this->data;

    }

    final public function __toString()
    {
        return get_class($this);
    }


    /**
     * Helpers
     */

    public function redirect($url)
    {
        Helpers::redirect($url);
    }

}