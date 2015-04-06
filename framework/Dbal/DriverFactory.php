<?php

namespace T4\Dbal;


class DriverFactory
{

    /**
     * @param string $driver
     * @return IDriver
     */
    public static function getDriver($driver)
    {
        static $drivers = [];
        if (!isset($drivers[$driver])) {
            $driverClassName = __NAMESPACE__ . '\\Drivers\\' . ucfirst($driver);
            $drivers[$driver] = new $driverClassName;
        }
        return $drivers[$driver];
    }

}