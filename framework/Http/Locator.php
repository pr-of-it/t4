<?php
namespace T4\Http;


class Locator

{
    public $locator;
    public function __construct()
    {
        $this->locator = new SxGeo(__DIR__ . '/SxGeoCity.dat');
    }

    public function getLocation()
    {
        if (isset($_SERVER['REMOTE_ADDR'])) {

            return $this->locator->getCityFull('REMOTE_ADDR');

        } else {

            return false;
        }
    }
}



