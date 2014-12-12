<?php

namespace T4\Http;

class Helpers
{

    protected static function getUniversalDomainName($domain)
    {
        return preg_replace('~^(www\.|)(.*)$~', '.$2', $domain);
    }

    public static function setCookie($name, $value, $expire=0, $allSubDomains = true)
    {
        $domain = \T4\Mvc\Application::getInstance()->request->domain;
        if ($allSubDomains)
            $domain = self::getUniversalDomainName($domain);
        setcookie($name, $value, $expire, '/', $domain, false, true);
    }

    public static function issetCookie($name)
    {
        return isset($_COOKIE[$name]);
    }

    public static function unsetCookie($name, $allSubDomains = true)
    {
        $domain = \T4\Mvc\Application::getInstance()->request->domain;;
        if ($allSubDomains)
            $domain = self::getUniversalDomainName($domain);
        self::setCookie($name, '', time()-60*60*24*30, '/', $domain, false, true);
        unset($_COOKIE[$name]);
    }

    public static function getCookie($name)
    {
        return $_COOKIE[$name];
    }

    public static function redirect($url)
    {
        $https = \T4\Mvc\Application::getInstance()->request->https;;
        header('Location: ' . ($https ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $url, true, 302);
        exit;
    }

}