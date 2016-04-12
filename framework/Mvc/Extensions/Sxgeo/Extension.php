<?php

namespace T4\Mvc\Extensions\Sxgeo;

class Extension
    extends \T4\Mvc\Extension
{

    /**
     * @var \SxGeo
     */
    protected $sxGeo;

    public function init()
    {
        require_once __DIR__ . '/src/SxGeo.php';
        $this->sxGeo = new \SxGeo(__DIR__ . '/src/SxGeoCity.dat');
    }

    public function getLocation($ip)
    {
        return $this->sxGeo->getCityFull($ip);
    }

}