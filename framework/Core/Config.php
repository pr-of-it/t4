<?php

namespace T4\Core;

class Config extends Std
{

    /**
     * @param array|string|null $data
     * @throws \T4\Core\Exception
     * @property $path string
     */
    private  $path;
    public function __construct($data = null)
    {
        if (null !== $data) {
            if (is_array($data)) {
                parent::__construct($data);
            } else {
                $this->load((string)$data);
            }
        }
    }

    /**
     * @param string $path
     * @return \T4\Core\Config
     * @throws \T4\Core\Exception
     */
    public function load($path)
    {
        $this->path=$path;
        if (!is_readable($path)) {
            throw new Exception('Config file ' . $path . ' is not found or is not readable');
        }
        return $this->fromArray(include($path));
    }

    private function arr_format($var, $indent = "")
    {
        switch (gettype($var)) {
            case "string":
                return "'".$var. "'";
            case "array":
                $indexed = array_keys($var) === range(0, count($var) - 1);
                $r = [];
                foreach ($var as $key => $value) {
                    $r[] = "$indent    " . ($indexed ? "" : $this->arr_format($key) . " => ") . $this->arr_format($value, "$indent    ");
                }
                return "[\n" . implode(",\n", $r) . "\n" . $indent . "]";
        }
    }

    public function save()
    {
        $str = $this->arr_format($this->toArray());
        file_put_contents($this->path, '<?php' . "\r\n" . "\r\n" . 'return ' . $str . ';');
    }

}