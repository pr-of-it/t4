<?php

namespace T4\Mvc;

/**
 * Class TApplicationPaths
 * @package \T4\Mvc
 * @mixin \T4\Mvc\Application
 */
trait TApplicationPaths
{

    /**
     * @return string
     */
    public function getPath()
    {
        return \ROOT_PATH_PROTECTED;
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
    public function getModulePath($module = null)
    {
        return $this->getPath() . (null === $module ? '' : DS . 'Modules' . DS . ucfirst($module));
    }

    /**
     * @param string $module
     * @param string $controller
     * @return string
     */
    public function getControllerTemplatesPath($module = null, $controller)
    {
        return $this->getModulePath($module) . DS . 'Templates' . DS . ucfirst($controller);
    }

    /**
     * @param string $module
     * @return bool
     */
    public function existsModule($module = null)
    {
        if (null === $module) {
            return true;
        }
        $modulePath = $this->getModulePath($module);
        return is_dir($modulePath) && is_readable($modulePath);
    }

}