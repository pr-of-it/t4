<?php

namespace T4\Mvc;

use T4\Core\Collection;
use T4\Core\Std;
use T4\Http\Helpers;
use T4\Orm\Model;

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
     * @var \T4\Mvc\ARenderer
     */
    public $view;

    final public function __construct()
    {
        $this->data = new Std();
        $this->app = Application::getInstance();
        // TODO: use View class
        $this->view = new \T4\Mvc\Renderers\Twig($this->getTemplatePaths());
        $this->view = new View('twig', $this->getTemplatePaths());
        $this->view->setController($this);
    }

    public function getTemplatePaths()
    {
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

    /**
     * Проверка прав на доступ к данному действию
     * @param $action
     * @return bool
     * @throws ControllerException
     */
    final public function checkAccess($action)
    {
        // Правила контроля доступа не заданы вообще либо не заданы для данного action
        if ( ! (method_exists($this, 'access') || (!empty($this->access) && !empty($this->access[$action]))) )
            return true;

        if (method_exists($this, 'access')) {
            $rule = $this->access($action);
        } else {
            $rule = $this->access[$action];
        }

        // Задано правило "требуется авторизация"
        if ('@' == $rule) {
            if (empty($this->app->user)) {
                throw new ControllerException('User is not logged in. Access denied.', self::ERROR_NO_ACCESS);
            } else {
                return true;
            }
        }

        // Правило представляет собой колл-бэк
        if (is_callable($rule)) {
            if ($rule()) {
                return true;
            } else {
                throw new ControllerException('Access denied.', self::ERROR_NO_ACCESS);
            }
        }

        // Задано сложное правило в виде массива. При этом автоматически требуется авторизация.
        if ( is_array($rule) ) {
            if (empty($this->app->user)) {
                throw new ControllerException('User is not logged in. Access denied.', self::ERROR_NO_ACCESS);
            }

            $user = $this->app->user;
            foreach ($rule as $column => $value) {

                // Каждый ключ массива - поле модели $this->app->user
                if (!isset($user->{$column})) {
                    throw new ControllerException('User has not property `' . $column . '`. Access denied.', self::ERROR_NO_ACCESS);
                }

                // Если это просто скалярное поле
                if (is_scalar($user->{$column})) {
                    if ($user->{$column} != $value) {
                        throw new ControllerException('User ' . $column . ' is invalid. Access denied.', self::ERROR_NO_ACCESS);
                    } else {
                        return true;
                    }
                    // Если это связанная модель - ищем в ней поле name
                } elseif ($user->{$column} instanceof Model) {
                    if (!isset($user->{$column}->name) || $user->{$column} != $value) {
                        throw new ControllerException('User ' . $column . ' is invalid. Access denied.', self::ERROR_NO_ACCESS);
                    } else {
                        return true;
                    }
                    // Если это коллекция моделей - ищем хотя бы одну с таким полем name
                } elseif ( is_array($user->{$column}) || $user->{$column} instanceof Collection ) {
                    foreach ($user->{$column} as $subModel) {
                        if (isset($subModel->name) && $subModel->name == $value) {
                            return true;
                        }
                    }
                    throw new ControllerException('User ' . $column . ' is invalid. Access denied.', self::ERROR_NO_ACCESS);
                }

            }
        }

        return true;

    }

    final public function action($name, $params = [])
    {
        $actionMethodName = 'action' . ucfirst($name);
        if (!method_exists($this, $actionMethodName)) {
            throw new ControllerException('Action ' . $name . ' is not found in controller ' . get_class($this));
        }

        // Проверяем правила контроля доступа к заданному action
        $this->checkAccess($name);

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