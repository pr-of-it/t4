<?php

namespace T4\Html;

use T4\Core\Std;
use T4\Dbal\Query;
use T4\Mvc\Application;
use T4\Mvc\View;

class Filters
    extends Std
{

    protected static $schema = [
    ];

    public function __construct($data = null)
    {
        foreach (static::$schema['filters'] as $key => $value) {
            $this->$key = new $value['class']($key, $data[$key] ?? null, $value['options'] ?? null);
        }
    }

    /**
     * @param \T4\Dbal\Query $query
     * @return \T4\Dbal\Query
     */
    public function modifyQuery(Query $query)
    {
        foreach ($this as $name => $filter) {
            /** @var \T4\Html\Filter $filter */
            $query = $filter->modifyQuery($query);
        }
        return $query;
    }

    /**
     * @deprecated
     * @param array $options
     * @return array
     */
    public function modifyQueryOptions($options = [])
    {
        foreach ($this as $name => $filter) {
            /** @var \T4\Html\Filter $filter */
            $options = $filter->getQueryOptions($options);
        }
        return $options;
    }

    public function renderForm(array $htmlOptions = []) : string
    {
        if (isset(static::$schema['template'])) {
            $dir = dirname(static::$schema['template']);
            $template = basename(static::$schema['template']);
        } else {
            $reflector = new \ReflectionClass(static::class);
            $filename = $reflector->getFileName();
            $dir = dirname($filename);
            $template = pathinfo(basename($filename), PATHINFO_FILENAME) . '.html';
        }

        $view = new View('Twig');
        $view->addTemplateRawPath($dir);
        return $view->render($template, [
            'filters' => $this,
            'html' => $htmlOptions,
        ]);
    }

}