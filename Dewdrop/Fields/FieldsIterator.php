<?php

namespace Dewdrop\Fields;

use Iterator;

class FieldsIterator implements Iterator
{
    private $fields;

    public function __construct(array $fields)
    {
        $this->fields = $fields;
    }

    /**
     * Get the current field during iteration.
     *
     * @return FieldInterface
     */
    public function current()
    {
        return current($this->fields);
    }

    /**
     * Get the ID of the current field to be used as the key during iteration.
     *
     * @return string
     */
    public function key()
    {
        return current($this->fields)->getId();
    }

    /**
     * Advance to the next field during iteration.
     *
     * @return FieldInterface
     */
    public function next()
    {
        return next($this->fields);
    }

    /**
     * Rewind the iteration pointer.
     *
     * @return void
     */
    public function rewind()
    {
        reset($this->fields);
    }

    /**
     * Check to see if we can continue with iteration.
     *
     * @return boolean
     */
    public function valid()
    {
        $key = key($this->fields);

        return (null !== $key && false !== $key);
    }

}
