<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 09.02.15
 * Time: 23:09
 */

namespace T4\Extensions\Captcha;

use T4\Core\Session;
class Extension
    extends \T4\Core\Extension
{

    /**
     * @var \SxGeo
     */
    protected $captcha;

    public function init()
    {
        require_once __DIR__ . DS . 'src' . DS . 'kcaptcha.php';
    }

    public function generateImage()
    {
        $this->captcha = new \ KCAPTCHA();
        Session::set('keystring', $this->captcha->getKeyString());
    }

    public function checkKeyString($string)
    {
        return Session::get('keystring') == $string;
    }

}