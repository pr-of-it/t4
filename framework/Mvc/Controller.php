<?php

namespace T4\Mvc;

use T4\Core\Std;
use T4\Http\E403Exception;
use T4\Http\E404Exception;
use T4\Http\Helpers;

abstract class Controller
{

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
     * @var \T4\Mvc\ARenderer
     */
    public $view;

    final public function __construct()
    {
        $this->data = new Std();
        $this->app = Application::getInstance();
        $this->view = new View('twig', $this->getTemplatePaths());
        $this->view->setController($this);
    }

    public function getTemplatePaths()
    {
        $templatesPaths = [];
        if ('' == $this->getModuleName()) {
            $templatesPaths[] = $this->app->getPath() . DS . 'Templates' . DS . str_replace('\\', DS, $this->getShortName());
        } else {
            $templatesPaths[] = $this->app->getPath() . DS . 'Modules' . DS . $this->getModuleName() . DS . 'Templates' . DS . str_replace('\\', DS, $this->getShortName());
        }
        if ('' != $this->getModuleName() && is_readable($moduleLayoutPath = $this->app->getPath() . DS . 'Layouts' . DS . $this->getModuleName())) {
            $templatesPaths[] = $moduleLayoutPath;
        }
        $templatesPaths[] = $this->app->getPath() . DS . 'Layouts';
        return $templatesPaths;
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
        $ret = [];
        $skip = true;
        foreach ($classNameParts as $part) {
            if (!$skip) {
                $ret[] = $part;
            }
            if ('Controllers' == $part) {
                $skip = false;
            }
        }
        return implode('\\', $ret);
    }

    protected function access($action)
    {
        return true;
    }

    protected function beforeAction($action)
    {
        return true;
    }

    protected function afterAction($action)
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
     * @throws E404Exception
     */
    final protected function getActionParameters($name)
    {
        $actionMethodName = 'action' . ucfirst($name);
        if (method_exists($this, $actionMethodName)) {
            $reflection = new \ReflectionMethod($this, $actionMethodName);
            return $reflection->getParameters();
        } else {
            throw new E404Exception('Action ' . $name . ' is not found in controller ' . get_class($this));
        }
    }

    final public function action($name, $params = [])
    {
        if ($params instanceof Std) {
            $params = $params->toArray();
        }
        $name = ucfirst($name);
        $actionMethodName = 'action' . $name;

        if (!method_exists($this, $actionMethodName)) {
            throw new E404Exception('Action ' . $name . ' is not found in controller ' . get_class($this));
        }

        if (method_exists($this, 'access')) {
            $check = $this->access($name);
            if (false === $check) {
                throw new E403Exception('Access denied');
            }
        }

        // Продолжаем выполнение действия только если из beforeAction не передано false
        if ($this->beforeAction($name)) {

            $p = [];
            $request = Application::getInstance()->request;
            foreach ($this->getActionParameters($name) as $param) {

                if (isset($params[$param->name])) {
                    $p[$param->name] = $params[$param->name];
                    unset($params[$param->name]);
                } elseif (isset($request->post[$param->name])) {
                    $p[$param->name] = $request->post[$param->name];
                } elseif (isset($request->get[$param->name])) {
                    $p[$param->name] = $request->get[$param->name];
                } elseif ($param->isDefaultValueAvailable()) {
                    $p[$param->name] = $param->getDefaultValue();
                } else {
                    throw new ControllerException('Missing argument ' . $param->name . ' for action ' . $actionMethodName);
                }

            }
            $p = array_merge($p, $params);

            call_user_func_array([$this, $actionMethodName], $p);
            $this->afterAction($name);
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

    protected function redirect($url)
    {
        Helpers::redirect($url);
    }

}