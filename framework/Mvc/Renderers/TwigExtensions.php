<?php

namespace T4\Mvc\Renderers;

use T4\Html\Helpers;
use T4\Mvc\Application;
use T4\Widgets\Factory as WidgetsFactory;

class TwigExtensions
    extends \Twig_Extension
{

    public function getName()
    {
        return 'T4';
    }

    public function getFunctions()
    {
        $app = Application::instance();
        return [

            'asset' => new \Twig_Function_Function(function ($path) use ($app) {return $app->assets->publish($path);}),
            'publish' => new \Twig_Function_Function(function ($path) use ($app) {$app->assets->publish($path);return '';}),
            'publishCss' => new \Twig_Function_Function(function () use ($app) { return $app->assets->getPublishedCss();},  ['is_safe' => ['html']]),
            'publishJs' => new \Twig_Function_Function(function () use ($app) { return $app->assets->getPublishedJs();},  ['is_safe' => ['html']]),

            'widget' =>  new \Twig_Function_Function(
                function ($name, $options=[]) {
                    $widget = WidgetsFactory::getInstance($name, $options);
                    return $widget->render();
                },  ['is_safe' => ['html']]
            ),

            'helper' => new \Twig_Function_Function(
                function ($name) {
                    return $this->helper($name, array_slice(func_get_args(), 1));
                },  ['is_safe' => ['html']]),

            'element' => new \Twig_Function_Function(
                function ($el, $name='', $options=[], $attrs=[]) {
                    return Helpers::element($el, $name, $options, $attrs);
                },  ['is_safe' => ['html']]),

            // DEPRECATED
            'selectTreeByModel' => new \Twig_Function_Function(
                function ($model, $selected = 0, $htmlOptions = [], $options = []) {
                    return Helpers::selectTreeByModel($model, $selected, $htmlOptions, $options);
                },  ['is_safe' => ['html']]
            ),

            // DEPRECATED
            'blockOptionInput' => new \Twig_Function_Function(
                function ($name, $settings, $value=null, $htmlOptions=[]) {
                    return Helpers::blockOptionInput($name, $settings, $value, $htmlOptions);
                },  ['is_safe' => ['html']]
            ),

        ];
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('repeat', 'str_repeat'),
            new \Twig_SimpleFilter('count', 'count'),
            new \Twig_SimpleFilter('basename', 'basename'),
            new \Twig_SimpleFilter('dirname', 'dirname'),
        ];
    }

    protected function helper($name, $args)
    {
        return call_user_func_array('\T4\Html\Helpers::'.$name, $args);
    }

}