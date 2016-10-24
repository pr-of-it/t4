<?php

namespace T4\Html;

use T4\Dbal\Connection;
use T4\Dbal\Query;
use T4\Mvc\Application;
use T4\Mvc\View;
use T4\Orm\Model;

abstract class Filter
{

    protected $name;
    protected $value;
    protected $options = [];

    public function __construct($name, $value, $options = [])
    {
        $this->name  = $name;
        $this->value = $value;
        if (!empty($options)) {
            $this->setOptions($options);
        }
    }

    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    protected function setOptions($options = [])
    {
        $this->options = $options;
        return $this;
    }

    protected function getConnection() : Connection
    {
        if (isset($this->options['model']) && $this->options['model'] instanceof Model) {
            return $this->options['model']->getDbConnection();
        } elseif (isset($this->options['connection']) && $this->options['connection'] instanceof Connection) {
            return $this->options['connection'];
        } elseif (isset($this->options['connection']) && is_string($this->options['connection'])) {
            return Application::instance()->db->{$this->options['connection']};
        } else {
            return Application::instance()->db->default;
        }
    }

    /**
     * @param \T4\Dbal\Query $query
     * @return \T4\Dbal\Query
     */
    abstract public function modifyQuery(Query $query) : Query;

    /**
     * @deprecated
     * @param array $options
     * @return array
     */
    abstract public function getQueryOptions($options = []) : array;

    public function renderFormElement(array $htmlOptions = []) : string
    {
        if (isset($this->options['template'])) {
            $dir = dirname($this->options['template']);
            $template = basename($this->options['template']);
        } else {
            $reflector = new \ReflectionClass(static::class);
            $filename = $reflector->getFileName();
            $dir = dirname($filename);
            $template = pathinfo(basename($filename), PATHINFO_FILENAME) . '.html';
        }

        $view = new View('Twig');
        $view->addTemplateRawPath($dir);

        $props = [];
        foreach (array_keys(get_object_vars($this)) as $prop) {
            $props[$prop] = $this->$prop;
        }

        return $view->render(
            $template,
            $props +
                [
                'html' => $htmlOptions,
                ]
        );
    }

}