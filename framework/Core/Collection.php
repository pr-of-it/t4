<?php

namespace T4\Core;

class Collection
    implements IArrayAccess, ICollection, \Serializable
{
    use TCollection;

    public function __construct($data = null)
    {
        if (null !== $data) {
            $this->fromArray($data);
        }
    }

    public function serialize()
    {
        return serialize($this->storage);
    }

    public function unserialize($serialized)
    {
        $this->storage = unserialize($serialized);
    }

}