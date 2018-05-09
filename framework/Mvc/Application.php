<?php

namespace T4\Mvc;

use T4\Cache\File;
use T4\Console\TRunCommand;
use T4\Core\Config;
use T4\Core\Exception;
use T4\Core\ISingleton;
use T4\Core\Session;
use T4\Core\Std;
use T4\Core\TSingleton;
use T4\Core\TStdGetSet;
use T4\Http\E403Exception;
use T4\Http\E404Exception;
use T4\Threads\Helpers;

/**
 * Class Application
 * @package T4\Mvc
 *
 * @property string $path
 * @property string $routeConfigPath
 *
 * @property \T4\Core\Config $config
 * @property \T4\Core\Std $extensions
 * @property \T4\Dbal\Connections|\T4\Dbal\Connection[] $db
 *
 * @property \T4\Mvc\IRouter $router
 * @property \T4\Http\Request $request
 *
 * @property \App\Models\User $user
 * @property \T4\Mvc\Module[] $modules
 * @property \T4\Mvc\AssetsManager $assets
 * @property \T4\Core\Flash $flash
 * @property \T4\Cache\Set $cache
 */
class Application
    implements
        ISingleton,
        IApplication
{
    use
        TStdGetSet,
        TSingleton,
        TApplicationPaths,
        TApplicationMagic;

    use TRunCommand;

    protected function init()
    {
        Session::init();
        $this->initExtensions();
    }

    protected function initExtensions()
    {
        $this->extensions = new Std;
        if (isset($this->config->extensions)) {
            foreach ($this->config->extensions as $extension => $config) {

                if (!empty($config->class)) {
                    $extensionClassName = $config->class;
                } else {
                    $extensionClassName = 'Extensions\\' . ucfirst($extension) . '\\Extension';
                    if (class_exists('\\App\\' . $extensionClassName)) {
                        $extensionClassName = '\\App\\' . $extensionClassName;
                    } else {
                        $extensionClassName = '\\T4\\Mvc\\' . $extensionClassName;
                    }
                }

                $this->extensions->{$extension} = new $extensionClassName($config);
                if (!isset($config->autoload) || true == $config->autoload) {
                    $this->extensions->{$extension}->init();
                }
            }
        }
    }

    public function run()
    {
        try {

            $this->init();

            try {
                $route = $this->router->parseRequest($this->request);
            } catch (RouterException $routerException) {
                throw new E404Exception($routerException->getMessage());
            }

            $this->runRoute($route);

        } catch (\T4\Http\Exception $e) {
            $this->actionHttpException($e);
        } catch (\Throwable $e) {
            echo $e->getMessage();
        }
    }

    /**
     * @param \T4\Mvc\Route|string $route
     * @param string $format
     * @throws ControllerException
     * @throws E403Exception
     * @throws Exception
     */
    public function runRoute(Route $route, $format = null)
    {
        if (null === $format) {
            $format = $route->format;
        }
        $controller = $this->createController($route->module, $route->controller);
        $controller->action($route->action, $route->params);
        $data = $controller->getData();

        $front = new Front($this, $controller);
        $front->output($route, $data, $format);
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
     * @param null $module
     * @param string $controller
     * @return bool
     */
    public function existsController($module = null, $controller)
    {
        if (class_exists($controller) && is_subclass_of($controller, Controller::class)) {
            return true;
        }
        $controllerClassName = (empty($module) ? '\\App\\Controllers\\' : '\\App\\Modules\\' . ucfirst($module) . '\\Controllers\\') . ucfirst($controller);
        return $this->existsModule($module) && class_exists($controllerClassName) && is_subclass_of($controllerClassName, Controller::class);
    }

    /**
     * @param string $module
     * @param string $controller
     * @throws \T4\Core\Exception
     * @return \T4\Mvc\Controller
     */
    public function createController($module = null, $controller)
    {
        if (!$this->existsController($module ?: null, $controller)) {
            throw new Exception('Controller ' . $controller . ' does not exist');
        }

        if (class_exists($controller) && is_subclass_of($controller, Controller::class)) {
            $controllerClass = $controller;
        } else {
            if (empty($module)) {
                $controllerClass = '\\App\\Controllers\\' . $controller;
            } else {
                $controllerClass = '\\App\\Modules\\' . ucfirst($module) . '\\Controllers\\' . ucfirst($controller);
            }
        }

        $controller = new $controllerClass;

        $view = new View('twig', $controller->getTemplatePaths());
        $controller->view = $view;
        $view->setController($controller);

        return $controller;
    }

    /**
     * @param string $path
     * @param string $template Шаблон блока
     * @param array $params Параметры, передаваемые блоку
     * @throws \T4\Core\Exception
     * @return string Результат рендера блока
     */
    public function callBlock($path, $template = '', $params = [])
    {
        $route = new Route($path);
        $route->params->merge($params);

        $canonicalPath = $route->toString();

        if (isset($this->config->blocks) && isset($this->config->blocks[$canonicalPath])) {
            $blockOptions = $this->config->blocks[$canonicalPath];
        } else {
            $blockOptions = [];
        }

        $getBlock = function () use ($template, $route) {
            $controller = $this->createController($route->module, $route->controller);
            $controller->action($route->action, $route->params);
            return $controller->view->render(
                $route->action . (!empty($template) ? '.' . $template : '') . '.block.html',
                $controller->getData()
            );
        };

        if (!empty($blockOptions['cache'])) {
            /** @var \T4\Cache\IDriver $cache */
            $cache = $this->cache->default;
            $key = md5($canonicalPath . serialize($route->params) . $template);
            if (!empty($blockOptions['cache']['time'])) {
                return $cache($key, $getBlock, $blockOptions['cache']['time']);
            } else {
                return $cache($key, $getBlock);
            }
        } else {
            return $getBlock();
        }

    }

    public function actionHttpException(\T4\Http\Exception $exception)
    {
        http_response_code($exception->getCode());
        if (!empty($this->config->errors[$exception->getCode()])) {
            $route = new Route($this->config->errors[$exception->getCode()]);
            $route->params->message = $exception->getMessage();
            $this->runRoute($route, 'html');
        } else {
            echo $exception->getMessage();
        }
    }
    
}