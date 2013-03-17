<?php

namespace Dewdrop\Fields;

use Dewdrop\Request;
use Dewdrop\Db\Field;

/**
 * @package Dewdrop
 */
class Edit
{
    /**
     * @var array
     */
    private $values;

    /**
     * @var array
     */
    private $fields = array();

    public function add(Field $field)
    {
        $this->fields[$field->getControlName()] = $field;

        return $this;
    }

    public function get($controlName)
    {
        return $this->fields[$controlName];
    }
}
