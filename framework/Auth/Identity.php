<?php

namespace T4\Auth;

abstract class Identity
{

    const ERROR_INVALID_EMAIL = 100;
    const ERROR_INVALID_PASSWORD = 101;

    abstract public function authenticate($data);

    abstract public function getUser();

    abstract public function login($user);

    abstract public function logout();
}