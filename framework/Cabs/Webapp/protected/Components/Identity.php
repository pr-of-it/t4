<?php

namespace App\Components;

use App\Models\User;
use T4\Mvc\Application;
use T4\Core\Session;
use T4\Crypt\Helpers;

class Identity
    extends \T4\Auth\Identity
{

    public function authenticate($data)
    {
        $user = User::findByEmail($data->email);
        if (empty($user)) {
            throw new Exception('User with email '.$data->email.' does not exists');
        }
        if (!Helpers::checkPassword($data->password, $user->password)) {
            throw new Exception('Invalid password');
        }
        $this->login($user);
        Application::getInstance()->user = $user;
        return $user;
    }

    public function getUser()
    {
        return Session::get('__user');
    }

    public function login($user)
    {
        Session::set('__user', $user);
    }

    public function logout()
    {
        Session::clear('__user');
    }

} 