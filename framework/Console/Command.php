<?php


namespace T4\Console;


class Command {

    const DEFAULT_ACTION = 'default';

    public function beforeAction()
    {
        return true;
    }

    public function afterAction()
    {

    }

}