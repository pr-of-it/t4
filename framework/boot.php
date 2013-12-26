<?php

define('ROOT_PATH', pathinfo(debug_backtrace()[0]['file'], PATHINFO_DIRNAME));
define('T4\\ROOT_PATH', __DIR__);
define('DS', DIRECTORY_SEPARATOR);

require T4\ROOT_PATH.DS.'autoload.php';