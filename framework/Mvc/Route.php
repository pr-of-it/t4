<?php

namespace T4\Mvc;

use T4\Core\Std;

/**
 * Class Route
 * @package T4\Mvc
 *
 * @property string domain
 * @property string basepath
 * @property string extension
 *
 * @property string module
 * @property string controller
 * @property string action
 * @property array params
 * @property string format
 *
 */
class Route
    extends Std
{

    const INTERNAL_PATH_PATTERN = '~^\/([^\/]*?)\/([^\/]*?)\/([^\/]*?)\/?(\((.*)\))?$~i';

    public function __construct($data = null, $simple = false)
    {
        if (null !== $data) {
            if (is_array($data)) {
                parent::__construct($data);
            } else {
                $this->fromString((string)$data, $simple);
            }
        }
    }

    public function fromString($str, $simple = false)
    {
        if (!preg_match(self::INTERNAL_PATH_PATTERN, $str, $m)) {
            throw new RouteException('Invalid route \'' . $str . '\'');
        };

        $params = isset($m[5]) ? $m[5] : '';
        if (!empty($params)) {
            $params = explode(',', $params);
            $p = [];
            foreach ($params as $pair) {
                list($name, $value) = explode('=', $pair);
                $p[$name] = $value;
            }
            $params = $p;
        } else $params = [];

        $this->module = ucfirst($m[1]);
        $this->controller = (!$simple && empty($m[2])) ? Router::DEFAULT_CONTROLLER : ucfirst($m[2]);
        $this->action = (!$simple && empty($m[3])) ? Router::DEFAULT_ACTION : ucfirst($m[3]);
        $this->params = new self($params);
    }

    public function toString($simple = true)
    {
        $base = '/' . $this->module . '/' .
            ($simple && ($this->controller == Router::DEFAULT_CONTROLLER || empty($this->controller)) ? '' : ($this->controller ?: Router::DEFAULT_CONTROLLER)) . '/' .
            ($simple && ($this->action == Router::DEFAULT_ACTION || empty($this->action)) ? '' : ($this->action ?: Router::DEFAULT_ACTION));
        return $base;
    }

    public function __toString()
    {
        return $this->toString();
    }

} 