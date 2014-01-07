<?php

define('DS', DIRECTORY_SEPARATOR);

define('ROOT_PATH', realpath(__DIR__.'/../../'));
define('ROOT_PATH_PROTECTED', realpath(ROOT_PATH.'/protected'));
define('ROOT_PATH_PUBLIC', pathinfo(debug_backtrace()[0]['file'], PATHINFO_DIRNAME));
define('T4\\ROOT_PATH', __DIR__);

require T4\ROOT_PATH.DS.'autoload.php';