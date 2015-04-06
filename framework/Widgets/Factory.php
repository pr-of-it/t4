<?php

namespace T4\Widgets;

class Factory
{

    /**
     * @param string $widget
     * @param array $options
     * @return \T4\Mvc\Widget
     */
    public static function getInstance($widget, $options = [])
    {
        // TODO: поиск виджетов приложения
        $className = '\\T4\\Widgets\\' . ucfirst($widget);
        return new $className($options);
    }

}