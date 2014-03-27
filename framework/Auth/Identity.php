<?php

namespace T4\Auth;

use App\Models\User;
use T4\Mvc\Application;
use T4\Core\Session;
use T4\Crypt\Helpers;

class Identity {

    public static function authenticate($email, $password)
    {
        $user = User::findByEmail($email);
        if (empty($user)) {
            throw new Exception('User with email '.$email.' does not exists');
        }
        if (!Helpers::checkPassword($password, $user->password)) {
            throw new Exception('Invalid password');
        }
        self::login($user);
        Application::getInstance()->user = $user;
        return $user;
    }

    public static function getUser()
    {
        return Session::get('__user');
    }

    public static function login($user)
    {
        Session::set('__user', $user);
    }

    public static function logout()
    {
        Session::clear('__user');
    }

}