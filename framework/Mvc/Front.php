<?php

namespace T4\Mvc;

use T4\Core\Exception;
use T4\Core\Std;

class Front
{

    const FORMATS = ['html', 'xml', 'json'];
    const FORMAT_DEFAULT = 'html';

    protected $app;

    public function __construct(IApplication $app)
    {
        $this->app = $app;
    }

    public function getTemplateFileName(Route $route)
    {
        return $route->action . '.' . $route->format;
    }

    public function output(Std $data, $format = self::FORMAT_DEFAULT)
    {
        if (!in_array($format, self::FORMATS)) {
            throw new Exception('Invalid output format');
        }

        switch ($format) {
            case 'json':
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode($data->toArray(), JSON_UNESCAPED_UNICODE);
                break;
            case 'xml':
                header('Content-Type: text/xml; charset=utf-8');
                //$controller->view->display($action . '.' . $format, $data);
                break;
            default:
            case 'html':
                header('Content-Type: text/html; charset=utf-8');
                //$controller->view->display($action . '.' . $format, $data);
                break;
        }
    }

}