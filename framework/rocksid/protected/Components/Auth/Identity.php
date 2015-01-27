<?php

namespace App\Components\Auth;

use App\Models\User;
use App\Models\UserSession;
use T4\Mvc\Application;
use T4\Auth\Exception;

class Identity
    extends \T4\Auth\Identity
{

    const AUTH_COOKIE_NAME = 'T4auth';

    public function authenticate($data)
    {
        if (empty($data->email)) {
            throw new Exception('Empty email', self::ERROR_INVALID_EMAIL);
        }
        if (empty($data->password)) {
            throw new Exception('Empty password', self::ERROR_INVALID_PASSWORD);
        }

        $user = User::findByEmail($data->email);
        if (empty($user)) {
            throw new Exception('User with email ' . $data->email . ' does not exists', self::ERROR_INVALID_EMAIL);
        }

        if (!\T4\Crypt\Helpers::checkPassword($data->password, $user->password)) {
            throw new Exception('Invalid password', self::ERROR_INVALID_PASSWORD);
        }

        $this->login($user);
        Application::getInstance()->user = $user;
        return $user;
    }

    public function getUser()
    {
        if (!\T4\Http\Helpers::issetCookie(self::AUTH_COOKIE_NAME))
            return null;

        $hash = \T4\Http\Helpers::getCookie(self::AUTH_COOKIE_NAME);
        $session = UserSession::findByHash($hash);
        if (empty($session)) {
            \T4\Http\Helpers::unsetCookie(self::AUTH_COOKIE_NAME);
            return null;
        }

        if ($session->userAgentHash != md5($_SERVER['HTTP_USER_AGENT'])) {
            $session->delete();
            \T4\Http\Helpers::unsetCookie(self::AUTH_COOKIE_NAME);
            return null;
        }

        return $session->user;
    }

    public function register($data)
    {
        if (empty($data->email)) {
            throw new Exception('Empty email', self::ERROR_INVALID_EMAIL);
        }
        if (empty($data->password)) {
            throw new Exception('Empty password', self::ERROR_INVALID_PASSWORD);
        }

        $user = User::findByEmail($data->email);
        if (!empty($user)) {
            throw new Exception('Email already exists', self::ERROR_INVALID_EMAIL);
        }

        $user = new User();
        $user->email = $data->email;
        $user->password = \T4\Crypt\Helpers::hashPassword($data->password);
        $user->save();

        return $user;
    }

    /**
     * @param \App\Models\User $user
     */
    public function login($user)
    {
        $app = Application::getInstance();
        $expire = isset($app->config->auth) && isset($app->config->auth->expire) ?
            time() + $app->config->auth->expire :
            0;
        $hash = md5(time() . $user->password);

        \T4\Http\Helpers::setCookie(self::AUTH_COOKIE_NAME, $hash, $expire);

        $session = new UserSession();
        $session->hash = $hash;
        $session->userAgentHash = md5($_SERVER['HTTP_USER_AGENT']);
        $session->user = $user;
        $session->save();
    }

    public function logout()
    {
        if (!\T4\Http\Helpers::issetCookie(self::AUTH_COOKIE_NAME))
            return;

        $hash = \T4\Http\Helpers::getCookie(self::AUTH_COOKIE_NAME);
        $session = UserSession::findByHash($hash);
        if (empty($session)) {
            \T4\Http\Helpers::unsetCookie(self::AUTH_COOKIE_NAME);
            return;
        }

        $session->delete();
        \T4\Http\Helpers::unsetCookie(self::AUTH_COOKIE_NAME);

        $app = Application::getInstance();
        $app->user = null;
    }

}