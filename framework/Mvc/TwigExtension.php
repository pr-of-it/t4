<?php

namespace T4\Mvc;

use T4\Html\Helpers;

class TwigExtension extends \Twig_Extension
{

    public function getName()
    {
        return 'T4';
    }

    public function getFunctions()
    {
        $app = Application::getInstance();
        return [

            'asset' => new \Twig_Function_Function($app->assets),
            'publish' => new \Twig_Function_Function(function ($path) use ($app) {$app->assets->publish($path);return '';}),
            'publishCss' => new \Twig_Function_Function(function () use ($app) { return $app->assets->getPublishedCss();},  ['is_safe' => ['html']]),
            'publishJs' => new \Twig_Function_Function(function () use ($app) { return $app->assets->getPublishedJs();},  ['is_safe' => ['html']]),

            'blockOptionInput' => new \Twig_Function_Function(
                    function ($name, $settings, $value=null, $htmlOptions=[]) use ($app) {
                        return Helpers::blockOptionInput($name, $settings, $value, $htmlOptions);
                    },  ['is_safe' => ['html']]
                ),

        ];
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('repeat', 'str_repeat'),
        ];
    }

}