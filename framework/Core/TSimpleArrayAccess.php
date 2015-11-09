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
        return $this->storage[$offset];
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

    public function offsetExists($offset)
    {
        return $this->innerIsset($offset);
    }

    public function offsetGet($offset)
    {
        return $this->innerGet($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->innerSet($offset, $value);
    }

    public function offsetUnset($offset)
    {
        $this->innerUnset($offset);
    }

    /*
     * --------------------------------------------------------------------------------
     */

    public function count()
    {
        return count($this->storage);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->storage);
    }

}