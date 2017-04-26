<?php

namespace T4\Core;

/**
 *
 * IArrayAccess interface vanilla implementation
 *
 * Trait TArrayAccess
 * @package T4\Core
 *
 * @implements \T4\Core\IArrayAccess
 * @implements \ArrayAccess
 * @implements \Countable
 * @implements \IteratorAggregate
 * @implements \T4\Core\IArrayable
 * @implements \Serializable
 * @implements \JsonSerializable
 */
trait TArrayAccess
// implements IArrayAccess
{

    protected $storage = [];

    /*
     * --------------------------------------------------------------------------------
     */

    /**
     * @param int|string $offset
     * @return bool
     */
    protected function innerIsset($offset)
    {
        return array_key_exists($offset, $this->storage);
    }

    /**
     * @param int|string $offset
     * @return mixed
     */
    protected function innerGet($offset)
    {
        return array_key_exists($offset, $this->storage) ? $this->storage[$offset] : null;
    }

    /**
     * @param int|string $offset
     * @param mixed $value
     */
    protected function innerSet($offset, $value)
    {
        if (null === $offset) {
            if (empty($this->storage)) {
                $offset = 0;
            } else {
                $offset = max(array_keys($this->storage))+1;
            }
        }
        $this->storage[$offset] = $value;
    }

    /**
     * @param int|string $offset
     */
    protected function innerUnset($offset)
    {
        unset($this->storage[$offset]);
    }

    /*
     * --------------------------------------------------------------------------------
     */

    /**
     * @param int|string $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->innerIsset($offset);
    }

    /**
     * @param int|string $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->innerGet($offset);
    }

    /**
     * @param int|string $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->innerSet($offset, $value);
    }

    /**
     * @param int|string $offset
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

    /*
     * --------------------------------------------------------------------------------
     */

    /**
     * @param iterable $data
     * @return $this
     */
    public function fromArray($data)
    {
        foreach ($data as $offset => $value) {
            $this->innerSet($offset, $value);
        }
        return $this;
    }

    /**
     * @return array
     */
    public function toArray() : array
    {
        return $this->storage;
    }

    /**
     * @return array
     */
    public function toArrayRecursive() : array
    {
        $data = [];
        foreach (array_keys($this->storage) as $key) {
            $value = $this->innerGet($key);
            if ($value instanceof IArrayable) {
                $data[$key] = $value->toArrayRecursive();
            } else {
                $data[$key] = $value;
            }
        }
        return $data;
    }

    /*
     * --------------------------------------------------------------------------------
     */

    /**
     * @return string
     */
    public function serialize()
    {
        return serialize($this->storage);
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $this->storage = unserialize($serialized);
    }

    /*
     * --------------------------------------------------------------------------------
     */

    /**
     * @return array
     */
    public function jsonSerialize ()
    {
        return $this->storage;
    }

}