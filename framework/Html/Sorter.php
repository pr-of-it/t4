<?php

namespace T4\Html;

use T4\Core\QueryString;
use T4\Core\Url;
use T4\Dbal\Query;
use T4\Mvc\View;

class Sorter
{

    const DEFAULT_VALUE = 'ASC';

    protected $name;
    protected $value;
    protected $options = [];

    public function __construct($name, $value = null, $options = [])
    {
        $this->name  = $name;
        $this->setValue($value);
        if (!empty($options)) {
            $this->setOptions($options);
        }
    }

    public function setValue($value)
    {
        $this->value = $value;
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

    protected function getColumnName()
    {
        return $this->options['columnName'] ?? $this->name;
    }

    /**
     * @param \T4\Dbal\Query $query
     * @return \T4\Dbal\Query
     */
    public function modifyQuery(Query $query): Query
    {
        if ( empty($this->value) ) {
            return $query;
        }
        $query->order($this->getColumnName() . ' ' . $this->getValue());
        return $query;
    }

    public function modifyQueryOptions($options = []) : array
    {
        if ( empty($this->value) ) {
            return $options;
        }
        $options['order'] = $this->getColumnName() . ' ' . $this->getValue();
        return $options;
    }

    public function modifyUrl(Url $url, $name = null, $value = null)
    {
        if (null === $name) {
            return $url;
        }
        $url = clone $url;
        $query = $url->query ?? new QueryString();
        $query['sort'] = [$name => $value];
        unset($query['page']);
        $url->query = $query;
        return $url;
    }

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
                'this' => $this,
                'html' => $htmlOptions,
            ]
        );
    }

}