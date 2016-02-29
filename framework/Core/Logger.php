<?php

namespace T4\Core;

use Psr\Log\AbstractLogger;

class Logger
    extends AbstractLogger
{
    protected $path;

    public function __construct(Std $config = null)
    {
        if (empty($config->path)) {
            throw new Exception('Empty path for logs');
        }
        $this->path = $config->path;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return null
     */
    public function log($level, $message, array $context = array())
    {
        $messageArray = [
            date('Y/m/d H:i:s')." [". $level . "] " . $message
        ];

        if (!empty($context)) {
            $messageArray[] = 'Context:';
            foreach ($context as $key => $value) {
                $messageArray[] =  $key . ' => ' . var_export($value, true);
            }
        }
        $trace = $this->getTrace();
        $messageArray[] = 'Trace:';
        $messageArray[] = $trace['trace'];

        $file = $this->path . DIRECTORY_SEPARATOR . 'application.log';
        if (!file_exists($this->path)) {
            mkdir($this->path, 0777, true);
        }

        file_put_contents(
            $file,
            implode("\n", $messageArray),
            FILE_APPEND
        );
    }

    protected function getTrace()
    {
        $result = [];
        $basePath = \T4\ROOT_PATH;
        ob_start();
        $trace = debug_backtrace();
        // skip the first 3 stacks as they do not tell the error position
        if (count($trace) > 3)
            $trace = array_slice($trace, 2);
        foreach ($trace as $i => $t) {
            if (!isset($t['file']))
                $t['file'] = 'unknown';
            if (!isset($t['line']))
                $t['line'] = 0;
            if (!isset($t['function']))
                $t['function'] = 'unknown';
            $fileName = str_replace($basePath, '', $t['file']);
            if (0 === $i) {
                $result['file'] = $fileName;
                $result['line'] = $t['line'];
            }
            echo "#$i $fileName({$t['line']}): ";
            if (isset($t['object']) && is_object($t['object']))
                echo get_class($t['object']) . '->';
            echo "{$t['function']}()\n";
        }
        $result['trace'] = ob_get_contents();
        ob_end_clean();

        return $result;
    }
}