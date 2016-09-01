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
        if (null !== $data) {
            foreach ($data as $key => $value) {
                if (!isset(static::$schema[$key])) {
                    throw new Exception('Неверное имя фильтра');
                }
                $this->$key = new static::$schema[$key]['class']($key, $value, static::$schema[$key]['options'] ?? null);
            }
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