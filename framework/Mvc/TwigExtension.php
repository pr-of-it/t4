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
        ];
    }

}