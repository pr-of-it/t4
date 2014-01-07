<?php

require realpath(__DIR__.DS.'..'.DS.'vendor'.DS.'autoload.php');

spl_autoload_register(function ($className) {
    if ( 'T4' == substr($className,0,2) ) {
        $className = str_replace('T4', '', $className);
        require T4\ROOT_PATH . str_replace('\\', DS, $className) . '.php';
    } elseif ( 'App' == substr($className,0,3) ) {
        $className = str_replace('App', '', $className);
        require ROOT_PATH_PROTECTED . str_replace('\\', DS, $className) . '.php';
    }
    return false;
});