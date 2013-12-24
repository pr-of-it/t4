<?php

spl_autoload_register(function ($className) {
    if ( 'T4' != substr($className,0,2) )
        return false;
    $className = str_replace('T4', '', $className);
    require __DIR__ . str_replace('\\', DIRECTORY_SEPARATOR, $className) . '.php';
});