<?php

namespace T4 {
    const ROOT_PATH = __DIR__;
    const VERSION = 0.1;
}

namespace {
    const DS = DIRECTORY_SEPARATOR;
}

namespace {
    if (!class_exists('\\T4\\Core\\Std')) {
        require T4\ROOT_PATH . DS . 'autoload.php';
    }
}