<?php

spl_autoload_register(function ($className) {

    if ('T4' == substr($className, 0, 2)) {
        $className = str_replace('T4', '', $className);
        $fileName = T4\ROOT_PATH . str_replace('\\', DS, $className) . '.php';
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