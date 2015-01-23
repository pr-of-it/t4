<?php

namespace T4\Extensions\Sxgeo;

class Extension
    extends \T4\Core\Extension
{

    /**
     * @var \SxGeo
     */
    protected $sxGeo;

    public function init()
    {
        require_once __DIR__ . DS . 'src' . DS . 'SxGeo.php';
        $this->sxGeo = new \SxGeo(__DIR__ . DS . 'src' . DS . 'SxGeoCity.dat');
    }

    public function getLocation($ip)
    {
        return $this->sxGeo->getCityFull($ip);
    }

}