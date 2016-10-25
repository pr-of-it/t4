<?php

namespace T4\Core;

/**
 * Full object-as-array access interface
 *
 * Interface IArrayAccess
 * @package T4\Core
 */
interface IArrayAccess
    extends \ArrayAccess, \Countable, \IteratorAggregate, IArrayable, \Serializable, \JsonSerializable
{

}