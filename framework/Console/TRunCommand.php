<?php

namespace T4\Console;

trait TRunCommand
{

    public function runCommand($cmd)
    {
        if (substr(php_uname(), 0, 7) == "Windows"){
            $res = popen('start "" /B '.$cmd, "r");
            if (false === $res) {
                return false;
            }
            pclose($res);
        } else {
            exec($cmd . " > /dev/null &", $output, $code);
            if (0 != $code) {
                return false;
            }
        }
        return true;
    }

}