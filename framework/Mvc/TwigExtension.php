<?php

namespace T4\Mvc;


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
        ];
    }

}