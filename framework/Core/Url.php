<?php

namespace T4\Core;

/**
 * Class Url
 * @package T4\Core
 * @property $protocol
 * @property $host
 * @property $port
 * @property $path
 * @property $query
 * @property $fragment
 */
class Url
    extends Std
{

    public function __construct($data = null)
    {
        if (null !== $data) {
            if (is_array($data)) {
                parent::__construct($data);
            } else {
                $this->fromString((string)$data);
            }
        }
    }

    public function fromString($url)
    {
        $url = parse_url($url);
        if (false === $url) {
            return $this;
        }
        $this->protocol = $url['scheme'] ?? null;
        $this->host = $url['host'] ?? null;
        $this->port = $url['port'] ?? null;
        $this->path = $url['path'] ?? null;

        if (null == $this->host && null !== $this->path) {
            $pathParts = explode('/', $this->path);
            if (false !== strpos($pathParts[0], '.')) {
                $this->host = $pathParts[0];
                unset($pathParts[0]);
                $this->path = '/' . implode('/', $pathParts);
            }
        }

        $this->query = isset($url['query']) ? new QueryString($url['query']) : null;
        $this->fragment = $url['fragment'] ?? null;
        return $this;
    }

    public function toString()
    {
        $ret = $this->protocol ? $this->protocol . '://' : '';
        $ret .= $this->host ? $this->host : '';
        $ret .= $this->port ? ':' . $this->port : '';
        $ret .= $this->path ? $this->path : '';
        $ret .= (string)$this->query ? '?' . (string)$this->query : '';
        $ret .= $this->fragment ? '#' . $this->fragment : '';
        return $ret;
    }

    public function __toString()
    {
        return $this->toString();
    }

    public function __clone()
    {
        if (is_object($this->query)) {
            $this->query = clone $this->query;
        }
    }

}