<?php

namespace T4\Http;

class Helpers
{

    protected static function getUniversalDomainName($domain)
    {
        if (false !== strpos($domain, '.')) {
            return preg_replace('~^(www\.|)(.*)$~', '.$2', $domain);
        } else {
            // @fix Chrome security policy bug
            return '';
        }
    }

    public static function setCookie($name, $value, $expire = 0, $allSubDomains = true)
    {
        $domain = \T4\Mvc\Application::getInstance()->request->host;
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
        $domain = \T4\Mvc\Application::getInstance()->request->host;;
        if ($allSubDomains)
            $domain = self::getUniversalDomainName($domain);
        setcookie($name, '', time() - 60 * 60 * 24 * 30, '/', $domain, false, true);
        unset($_COOKIE[$name]);
    }

    public static function getCookie($name)
    {
        return $_COOKIE[$name];
    }

    public static function redirect($url)
    {
        if (false === strpos($url, 'http') && false === strpos($url, 'https')) {
            $protocol = \T4\Mvc\Application::getInstance()->request->protocol;
            $host = \T4\Mvc\Application::getInstance()->request->host;
            header('Location: ' . $protocol . '://' . $host . $url, true, 302);
        } else {
            header('Location: ' . $url, true, 302);
        }
        exit;
    }

}