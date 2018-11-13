<?php

namespace T4;

use T4\Console\Application as CliApplication;
use T4\Core\IApplication;
use T4\Mvc\Application as MvcApplication;

/**
 * Возвращает экземпляр приложения
 * @return IApplication
 */
function app(): IApplication
{
    return 0 === strpos(PHP_SAPI, 'cli') ? CliApplication::instance() : MvcApplication::instance();
}
