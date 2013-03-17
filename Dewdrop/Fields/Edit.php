<?php

namespace Dewdrop\Fields;

use Dewdrop\Db\Field;
use Dewdrop\Exception;

/**
 * @package Dewdrop
 */
class Edit
{
    /**
     * @var array
     */
    private $fields = array();

    public function add(Field $field, $groupName = null)
    {
        if (null === $groupName) {
            $this->fields[$field->getControlName()] = $field;
        } else {
            $fieldIndex = $groupName . ':' . $field->getName();
            $field->setControlName($fieldIndex);
            $this->fields[$fieldIndex] = $field;
        }

        return $this;
    }

    public function get($controlName)
    {
        if (!$this->has($controlName)) {
            throw new Exception("Unknown field \"{$controlName}\" requested");
        }

        return $this->fields[$controlName];
    }

    public function has($controlName)
    {
        return array_key_exists($controlName, $this->fields);
    }

    public function setValues(array $values)
    {
        foreach ($values as $key => $value) {
            if ($this->has($key)) {
                $this->get($key)->setValue($value);
            }
        }

        // When not checked, checkboxes are excluded from POST in full.
        // This loop works around that quirk.
        foreach ($this->fields as $field) {
            if ($field->isType('tinyint') && !array_key_exists($field->getControlName(), $values)) {
                $field->setValue(0);
            }
        }

        return $this;
    }
}
