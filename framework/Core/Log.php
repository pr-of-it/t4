<?php

namespace T4\Core;


class Log
{

    //const LOG_FILE = ROOT_PATH_PROTECTED.DS.'log.txt';
    const LOG_FORMAT = '[%time] %s';

    public static function write($message)
    {
        $logFormat = str_replace(['%time'], [date('H:i:s')], self::LOG_FORMAT);
        file_put_contents(ROOT_PATH_PROTECTED . DS . 'log.txt', sprintf($logFormat, $message) . "\n", FILE_APPEND);
    }

}