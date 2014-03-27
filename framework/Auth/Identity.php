<?php

namespace T4\Auth;

abstract class Identity {

    abstract public function authenticate($data);

    abstract public function getUser();

    abstract public function login($user);

    abstract public function logout();

}