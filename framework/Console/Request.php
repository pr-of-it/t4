<?php

namespace T4\Console;

use T4\Core\Std;

/**
 * Class Request
 * @package T4\Console
 * @property string $command
 * @property array $arguments
 *
 * @property array $options
 */
class Request
    extends Std
{

    const OPTION_PATTERN = '~^--([^=]+)={0,1}([^=]*)$~';

    public function __construct()
    {
        $arguments = array_slice($_SERVER['argv'], 1);
        $this->command = array_shift($arguments);
        $this->arguments = $arguments;
        $options = $this->parseOptions($this->arguments);
        $this->options = $options ? new Std($options) : [];
    }

    protected function parseOptions($arguments)
    {
        $options = [];
        foreach ($arguments as $arg) {
            if (preg_match(self::OPTION_PATTERN, $arg, $m)) {
                $options[$m[1]] = $m[2] ?: true;
            }
        }
        return $options;
    }

}