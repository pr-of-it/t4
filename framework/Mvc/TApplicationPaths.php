<?php

namespace T4\Mvc;

trait TApplicationPaths
{

    public $path = \ROOT_PATH_PROTECTED;

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getRouteConfigPath()
    {
        return $this->getPath() . DS . 'routes.php';
    }

    /**
     * @param string $module
     * @return string
     */
    public function getModulePath($module = '')
    {
        return $this->getPath() . (empty($module) ? '' : DS . 'Modules' . DS . ucfirst($module));
    }

    /**
     * @param string $module
     * @param string $controller
     * @return string
     */
    public function getControllerTemplatesPath($module = '', $controller = Router::DEFAULT_CONTROLLER)
    {
        return $this->getModulePath($module) . DS . 'Templates' . DS . ucfirst($controller);
    }

    /**
     * @param string $module
     * @return bool
     */
    public function existsModule($module = '')
    {
        if (empty($module))
            return true;
        $modulePath = $this->getModulePath($module);
        return is_dir($modulePath) && is_readable($modulePath);
    }

    /**
     * @param string $module
     * @param string $controller
     * @return bool
     */
    public function existsController($module = '', $controller = Router::DEFAULT_CONTROLLER)
    {
        $controllerClassName = (empty($module) ? '\\App\\Controllers\\' : '\\App\\Modules\\' . ucfirst($module) . '\\Controllers\\') . ucfirst($controller);
        return $this->existsModule($module) && class_exists($controllerClassName) && is_subclass_of($controllerClassName, '\T4\Mvc\Controller');
    }

    /**
     * @param string $module
     * @param string $controller
     * @param string $action
     * @return bool
     */
    public function existsActionView($module = '', $controller = Router::DEFAULT_CONTROLLER, $action = Router::DEFAULT_ACTION)
    {
        $controllerTemplatesPath = $this->getControllerTemplatesPath($module, $controller);
        if (!is_dir($controllerTemplatesPath) || !is_readable($controllerTemplatesPath))
            return false;
        return count(glob($controllerTemplatesPath . DS . ucfirst($action) . '.*')) > 0;
    }

}