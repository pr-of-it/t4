<?php

namespace T4\Extensions\Captcha;

use T4\Core\Session;

class Extension
    extends \T4\Core\Extension
{

    public function init()
    {
        require_once __DIR__ . DS . 'src' . DS . 'kcaptcha.php';
    }

    public function generateImage()
    {
        $captcha = new \KCAPTCHA();
        Session::set('keystring', $captcha->getKeyString());
        die;
    }

    public function checkKeyString($string)
    {
        return Session::get('keystring') == $string;
    }

}