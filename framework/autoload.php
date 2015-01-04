<?php

if (!is_readable(\T4\ROOT_PATH . DS . '..' . DS . 'vendor')) {
    die('Install composer depedencies first!');
}

require realpath(\T4\ROOT_PATH . DS . '..' . DS . 'vendor' . DS . 'autoload.php');

spl_autoload_register(function ($className) {

    if ('T4' == substr($className, 0, 2)) {
        $className = str_replace('T4', '', $className);
        $fileName = T4\ROOT_PATH . str_replace('\\', DS, $className) . '.php';
    } elseif ('App' == substr($className, 0, 3)) {
        $className = preg_replace('~^App~', '', $className);
        $fileName = ROOT_PATH_PROTECTED . str_replace('\\', DS, $className) . '.php';
    } else {
        return false;
    }

    if (is_readable($fileName)) {
        require $fileName;
        return true;
    } else {
        return false;
    }

});