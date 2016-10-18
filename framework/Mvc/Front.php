<?php

namespace T4\Mvc;

use T4\Core\Exception;
use T4\Core\IArrayable;

class Front
{

    const FORMATS = ['html', 'xml', 'json'];
    const FORMAT_DEFAULT = 'html';

    protected $app;
    protected $controller;

    public function __construct(IApplication $app, Controller $controller = null)
    {
        $this->app = $app;
        $this->controller = $controller;
    }

    public function getTemplateFileName(Route $route, $format = null)
    {
        $format = $format ?: $route->format;
        return $route->action . '.' . $format;
    }

    /**
     * @param \T4\Mvc\Route $route
     * @param \T4\Core\IArrayable|\JsonSerializable $data
     * @param string|null $format
     * @throws \T4\Core\Exception
     * @throws \InvalidArgumentException
     */
    public function output(Route $route, $data, $format = null)
    {
        $format = $format ?: $route->format;
        $format = $format ?: self::FORMAT_DEFAULT;
        if (!in_array($format, self::FORMATS)) {
            throw new Exception('Invalid output format');
        }

        if (!($data instanceof IArrayable) && !($data instanceof \JsonSerializable)) {
            throw new \InvalidArgumentException('Argument 2 passed to output() must be an instance of ' . IArrayable::class . ' or ' . \JsonSerializable::class . ', ' . get_class($data) . ' given');
        }

        $template = $this->getTemplateFileName($route, $format);

        switch ($format) {
            case 'json':
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(($data instanceof \JsonSerializable) ? $data : $data->toArray(), JSON_UNESCAPED_UNICODE);
                break;
            case 'xml':
                header('Content-Type: text/xml; charset=utf-8');
                $this->controller->view->display($template, $data);
                break;
            default:
            case 'html':
                header('Content-Type: text/html; charset=utf-8');
                $this->controller->view->display($template, $data);
                break;
        }
    }

}