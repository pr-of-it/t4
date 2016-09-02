<?php

namespace T4\Html;

use T4\Core\Exception;
use T4\Core\Std;
use T4\Mvc\Application;

class Filters
    extends Std
{

    protected static $schema = [
    ];

    public function __construct($data = null)
    {
        foreach (static::$schema as $key => $value) {
            $this->$key = new $value['class']($key, $data[$key] ?? null, $value['options'] ?? null);
        }
    }

    public function modifyQueryOptions($options = [])
    {
        $app = Application::instance();
        foreach ($this as $name => $filter) {
            $options = $filter->getQueryOptions($app->db->{static::$schema[$name]['connection'] ?? 'default'}, $options);
        }
        return $options;
    }

}