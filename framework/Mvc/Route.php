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

    public function __construct($data=null)
    {
        if (null !== $data) {
            if (is_array($data)) {
                parent::__construct($data);
            }
        }
    }

    public function makeString($simple=true)
    {
        $base = '/' . $this->module . '/' .
        ($simple && ($this->controller == Router::DEFAULT_CONTROLLER || empty($this->controller)) ? '' : ($this->controller ?: Router::DEFAULT_CONTROLLER)) . '/' .
        ($simple && ($this->action == Router::DEFAULT_ACTION || empty($this->action)) ? '' : ($this->action ?: Router::DEFAULT_ACTION));
        return $base;
    }

    public function __toString()
    {
        return $this->makeString();
    }

} 