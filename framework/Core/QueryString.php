<?php

namespace T4\Core;

class QueryString
  extends Collection
{

    public function __construct($data = null)
    {
        if (null !== $data && is_string($data)) {
            $this->fromString($data);
        } else {
            parent::__construct($data);
        }
    }

    public function fromString(string $string)
    {
        parse_str($string, $data);
        $this->fromArray($data);
        return $this;
    }

    public function __toString()
    {
        return $this->toString();
    }

    public function toString()
    {
        return http_build_query($this->toArrayRecursive());
    }
    
}