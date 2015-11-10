<?php

namespace T4\Core;

class Collection
    implements IArrayAccess, ICollection
{
    use TCollection;

    public function __construct($data = null)
    {
        if (null !== $data) {
            $this->fromArray($data);
        }
    }

}