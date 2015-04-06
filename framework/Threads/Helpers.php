<?php

namespace T4\Threads;

class Helpers
{

    /**
     * @param callable $callback
     * @param array $args
     * @throws Exception
     * @return int Child process PID
     */
    static public function run(callable $callback, $args = [])
    {
        if (!function_exists('pcntl_fork')) {
            throw new Exception('No pcntl is installed');
        }

        $pid = pcntl_fork();
        if (-1 == $pid) {
            throw new Exception('pcntl_fork() error: ' . pcntl_strerror(pcntl_get_last_error()));
        }
        if (0 != $pid) {
            return $pid;
        } else {

            register_shutdown_function(function () {
                ob_end_clean();
                posix_kill(getmypid(), SIGKILL);
            });

            if (empty($args)) {
                call_user_func($callback);
            } else {
                call_user_func_array($callback, $args);
            }

            exit(0);

        }
    }

} 