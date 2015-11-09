<?php

namespace T4\Core;

/**
 * Trait TSimpleArrayAccess
 * @package T4\Core
 *
 * @implements \ArrayAccess
 * @implements \Countable
 * @implements \IteratorAggregate
 */
trait TSimpleArrayAccess
{

    protected $storage = [];

    /*
     * --------------------------------------------------------------------------------
     */

    protected function innerIsset($offset)
    {
        return array_key_exists($offset, $this->storage);
    }

    protected function innerGet($offset)
    {
        return isset($this->storage[$offset]) ? $this->storage[$offset] : null;
    }

    protected function innerSet($offset, $value)
    {
        if ('' == $offset) {
            if (empty($this->storage)) {
                $offset = 0;
            } else {
                $offset = max(array_keys($this->storage))+1;
            }
        }
        $this->storage[$offset] = $value;
    }

    protected function innerUnset($offset)
    {
        unset($this->storage[$offset]);
    }

    /*
     * --------------------------------------------------------------------------------
     */

    /**
     * @param $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->innerIsset($offset);
    }

    /**
     * @param $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->innerGet($offset);
    }

    /**
     * @param $offset
     * @param $value
     */
    public function offsetSet($offset, $value)
    {
        $this->innerSet($offset, $value);
    }

    /**
     * @param $offset
     */
    public function offsetUnset($offset)
    {
        $this->innerUnset($offset);
    }

    /*
     * --------------------------------------------------------------------------------
     */

    /**
     * @return int
     */
    public function count()
    {
        return count($this->storage);
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->storage);
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->storage);
    }

}